<?php

namespace Tests\Feature\Api;

use App\Api\ApiClass;
use App\Models\Avatar;
use App\Models\BotCommand;
use App\Models\BotConfig;
use App\Models\DetailRequest;
use App\Models\Message;
use App\Models\Notecard;
use App\Models\NotecardMail;
use App\Models\Notice;
use App\Models\NoticeNotecard;
use App\Models\Package;
use App\Models\QueuedEvent;
use App\Models\RentalNoticeOptOut;
use App\Models\SlConfig;
use App\Services\BotService;
use App\Services\NoticeLevels;
use App\Support\SystemApiKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesStreamAdminData;
use Tests\Support\SignsApiRequests;
use Tests\TestCase;

/** The owner's own server objects: mail, events, details, bot commands, notices and notecards. */
class OwnerApiTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;
    use SignsApiRequests;

    private function owner(string $path, array $data = [])
    {
        return $this->api($path, $data, ApiClass::Owner);
    }

    public function test_the_mail_server_takes_one_message_at_a_time(): void
    {
        $this->owner('api/v3/owner/mail/next')->assertOk()->assertExactJson(['ok' => true, 'message' => 'nowork', 'data' => ['message' => null]]);

        $avatar = $this->avatar('Mail Target');
        Message::create(['avatar_id' => $avatar->id, 'message' => 'first']);
        Message::create(['avatar_id' => $avatar->id, 'message' => 'second']);

        $this->owner('api/v3/owner/mail/next')->assertOk()->assertJson(['message' => 'ok'])
            ->assertJsonPath('data.message', ['avatarKey' => $avatar->avatar_uuid, 'text' => 'first']);
        $this->assertSame(['second'], Message::pluck('message')->all());
    }

    public function test_the_events_server_takes_the_oldest_event(): void
    {
        $this->owner('api/v3/owner/events/next')->assertOk()->assertJsonPath('data.event', null);
        QueuedEvent::create(['event_name' => 'RentalStart', 'event_message' => '{"uid":"abc"}', 'event_unixtime' => time()]);

        $this->owner('api/v3/owner/events/next')->assertOk()->assertJsonPath('data.event', ['name' => 'RentalStart', 'message' => '{"uid":"abc"}']);
        $this->assertSame(0, QueuedEvent::count());
    }

    public function test_the_details_server_sends_every_request_as_an_im(): void
    {
        $this->owner('api/v3/owner/details/process')->assertOk()->assertJson(['message' => 'nowork'])->assertJsonPath('data.processed', 0);
        $rental = $this->rental(avatar: $this->avatar('Detail Client'));
        DetailRequest::create(['rental_id' => $rental->id]);

        $this->owner('api/v3/owner/details/process')->assertOk()->assertJsonPath('data.processed', 1);

        $message = Message::where('avatar_id', $rental->avatar_id)->sole();
        $this->assertStringContainsString('Package: Test package', $message->message);
        $this->assertStringContainsString('stream.test', $message->message);
        $this->assertSame(0, DetailRequest::count());
    }

    public function test_bot_commands_go_to_the_bot_signed(): void
    {
        $this->owner('api/v3/owner/bot-commands/next')->assertOk()->assertJson(['message' => 'nowork']);
        app(BotService::class)->queue('IM', ['aaa', 'bbb']);

        $this->owner('api/v3/owner/bot-commands/next')->assertOk()->assertJson(['message' => 'passed command to mail server'])->assertJsonPath('data.via', 'mail');

        $line = Message::where('avatar_id', BotConfig::findOrFail(1)->avatar_id)->sole()->message;
        $this->assertMatchesRegularExpression('/^IM\|\|\|aaa~#~bbb\|\|\|(\d+)@@@([0-9a-f]{64})$/', $line);
        preg_match('/\|\|\|(\d+)@@@(.+)$/', $line, $m);
        $this->assertSame(hash_hmac('sha256', "IM\naaa~#~bbb\n".$m[1], BotConfig::findOrFail(1)->secret), $m[2]);
        $this->assertSame(0, BotCommand::count());
    }

    public function test_bot_commands_wait_when_the_bot_has_a_pending_message(): void
    {
        app(BotService::class)->queue('IM', ['aaa']);
        Message::create(['avatar_id' => BotConfig::findOrFail(1)->avatar_id, 'message' => 'still waiting']);

        $this->owner('api/v3/owner/bot-commands/next')->assertStatus(422)->assertJson(['message' => 'Too many pending mail commands to the bot']);
        $this->assertSame(1, BotCommand::count(), 'the command stays queued');
    }

    public function test_bot_commands_can_be_posted_to_a_bot_over_http(): void
    {
        BotConfig::whereKey(1)->update(['http_mode' => true, 'http_url' => 'https://bot.test/', 'secret' => 'botsecret1']);
        app(BotService::class)->queue('GroupInvite', ['grp', 'avatar']);

        Http::fake(['bot.test/*' => Http::sequence()
            ->push(['Key' => true, 'Value' => json_encode(['status' => true, 'reply' => 'invited'])])
            ->push(['Key' => true, 'Value' => json_encode(['status' => false, 'reply' => 'no such group'])])]);
        $this->owner('api/v3/owner/bot-commands/next')->assertOk()->assertJson(['message' => 'invited'])->assertJsonPath('data.via', 'http');
        Http::assertSent(fn ($request) => $request['commandName'] === 'GroupInvite' && $request['args'] === 'grp~#~avatar'
            && $request['signing'] === hash_hmac('sha256', "GroupInvite\ngrp~#~avatar\n".$request['unixtime'], 'botsecret1'));

        app(BotService::class)->queue('GroupInvite', ['grp', 'avatar']);
        $this->owner('api/v3/owner/bot-commands/next')->assertStatus(422)->assertJson(['message' => 'The bot said: no such group']);
        $this->assertSame(1, BotCommand::count(), 'a failed command is tried again');
    }

    public function test_the_notice_server_moves_one_rental_to_its_next_notice(): void
    {
        $this->owner('api/v3/owner/notices/next')->assertOk()->assertJson(['message' => 'nowork'])->assertJsonPath('data.changed', false);
        $rental = $this->rental(hoursLeft: 20, avatar: $this->avatar('Notice Client'));

        $this->owner('api/v3/owner/notices/next')->assertOk()->assertJsonPath('data.changed', true)
            ->assertJsonPath('data.rentalUid', $rental->rental_uid)->assertJsonPath('data.notice', '1 day notice');

        $this->assertSame(4, $rental->refresh()->notice_id);
        $this->assertStringContainsString('stream.test', Message::where('avatar_id', $rental->avatar_id)->sole()->message);
        $this->owner('api/v3/owner/notices/next')->assertOk()->assertJsonPath('data.changed', false);

        $rental->update(['expire_unixtime' => time() - 60]);
        $this->owner('api/v3/owner/notices/next')->assertOk()->assertJsonPath('data.notice', 'Expired');
        $this->assertSame(NoticeLevels::EXPIRED, $rental->refresh()->notice_id);
    }

    public function test_a_rental_that_opted_out_gets_no_im(): void
    {
        $rental = $this->rental(hoursLeft: 20);
        RentalNoticeOptOut::create(['rental_id' => $rental->id, 'notice_id' => 4]);

        $this->owner('api/v3/owner/notices/next')->assertOk()->assertJsonPath('data.changed', true);

        $this->assertSame(4, $rental->refresh()->notice_id);
        $this->assertSame(0, Message::count());
    }

    public function test_a_notice_can_send_a_static_notecard(): void
    {
        $card = NoticeNotecard::create(['name' => 'Renew card', 'missing' => false]);
        Notice::findOrFail(4)->update(['notice_notecard_id' => $card->id]);
        $rental = $this->rental(hoursLeft: 20);

        $this->owner('api/v3/owner/notices/next')->assertOk()
            ->assertJsonPath('data.staticNotecard', ['name' => 'Renew card', 'avatarKey' => $rental->avatar->avatar_uuid]);

        $card->update(['missing' => true]);
        $rental->update(['notice_id' => 10]);
        $this->owner('api/v3/owner/notices/next')->assertOk()->assertJsonPath('data.staticNotecard', null);
    }

    public function test_the_notice_server_hands_out_queued_notecards_and_drops_bad_ones(): void
    {
        $this->owner('api/v3/owner/notecards/next')->assertOk()->assertJson(['message' => 'nowork'])->assertJsonPath('data.recheck', false);
        $avatar = $this->avatar('Card Target');
        $card = NoticeNotecard::create(['name' => 'Welcome card', 'missing' => false]);
        $missing = NoticeNotecard::create(['name' => 'Gone card', 'missing' => true]);
        NotecardMail::create(['avatar_id' => $avatar->id, 'notice_notecard_id' => $missing->id]);
        NotecardMail::create(['avatar_id' => $avatar->id, 'notice_notecard_id' => $card->id]);

        $this->owner('api/v3/owner/notecards/next')->assertOk()->assertJson(['message' => 'Missing notecard please recheck'])
            ->assertJsonPath('data.notecard', null)->assertJsonPath('data.dropped', true);
        $this->assertSame(1, NotecardMail::count(), 'the bad entry is gone for good');

        $this->owner('api/v3/owner/notecards/next')->assertOk()->assertJson(['message' => 'send'])
            ->assertJsonPath('data.notecard', ['name' => 'Welcome card', 'avatarKey' => $avatar->avatar_uuid]);
    }

    public function test_the_notecard_inventory_is_synced(): void
    {
        NoticeNotecard::create(['name' => 'Kept', 'missing' => false]);
        NoticeNotecard::create(['name' => 'Gone and unused', 'missing' => true]);
        $returned = NoticeNotecard::create(['name' => 'Came back', 'missing' => true]);
        Package::query()->update(['welcome_notecard_id' => 1]);

        $this->owner('api/v3/owner/notecards/sync', ['notecards' => ['Kept', 'Came%20back', 'Brand%20new', 'none']])
            ->assertOk()->assertJsonPath('data.purged', 1);

        $this->assertSame(['Brand new', 'Came back', 'Kept'], NoticeNotecard::whereKeyNot(1)->orderBy('name')->pluck('name')->all());
        $this->assertFalse((bool) $returned->refresh()->missing);

        $this->owner('api/v3/owner/notecards/sync', ['notecards' => []])->assertOk();
        $this->assertSame(3, NoticeNotecard::whereKeyNot(1)->where('missing', true)->count(), 'everything not reported is missing');
        $this->owner('api/v3/owner/notecards/sync', [])->assertStatus(422);
    }

    public function test_the_bot_is_asked_to_fetch_notecards_when_some_are_waiting(): void
    {
        $this->owner('api/v3/owner/bot/notecards/sync')->assertStatus(403)->assertJson(['code' => 'disabled']);
        BotConfig::whereKey(1)->update(['notecards' => true]);

        $this->owner('api/v3/owner/bot/notecards/sync')->assertOk()->assertJsonPath('data.state', 'nowork');
        $this->assertSame(0, BotCommand::count());

        $rental = $this->rental();
        Notecard::create(['rental_id' => $rental->id, 'as_notice' => false]);
        $this->owner('api/v3/owner/bot/notecards/sync')->assertOk()->assertJson(['message' => 'requested'])->assertJsonPath('data.state', 'requested');
        $this->owner('api/v3/owner/bot/notecards/sync')->assertOk()->assertJson(['message' => 'already requested']);

        $command = BotCommand::sole();
        $this->assertSame('FetchNextNotecard', $command->command);
        $args = json_decode($command->args, true);
        $this->assertSame(rtrim(config('app.url'), '/').'/api/v3/bot/notecards/next', $args[0]);
        $this->assertSame(SystemApiKey::current(SlConfig::findOrFail(1)->http_inbound_secret, '/api/v3/bot/notecards/next'), $args[1]);
    }

    public function test_the_support_code_is_the_first_part_of_the_key(): void
    {
        SlConfig::whereKey(1)->update(['payment_key' => 'ABCDEFGH:1999999999*abc']);

        $this->owner('api/v3/owner/support-code')->assertOk()->assertJsonPath('data.code', 'ABCDEFGH:1999999999');
    }

    public function test_none_of_it_is_open_to_vendors(): void
    {
        foreach (['mail/next', 'events/next', 'details/process', 'bot-commands/next', 'notices/next', 'notecards/next', 'notecards/sync', 'bot/notecards/sync', 'support-code'] as $path) {
            $this->api('api/v3/owner/'.$path)->assertStatus(403);
        }
        $this->assertInstanceOf(Avatar::class, Avatar::find(1));
    }
}
