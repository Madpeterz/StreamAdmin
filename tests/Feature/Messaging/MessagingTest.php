<?php

namespace Tests\Feature\Messaging;

use App\Livewire\Bot;
use App\Livewire\Notices;
use App\Livewire\Outbox;
use App\Livewire\Templates;
use App\Models\Ban;
use App\Models\BotCommand;
use App\Models\BotConfig;
use App\Models\Message;
use App\Models\Notice;
use App\Models\Package;
use App\Models\RentalNoticeOptOut;
use App\Models\Template;
use App\Services\NoticeLevels;
use App\Support\Swaps;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Support\CreatesStreamAdminData;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->staff());
    }

    public function test_pages_render(): void
    {
        $this->rental();
        foreach (['/outbox', '/outbox/compose', '/notices', '/notices/create', '/notices/1', '/notices/6', '/templates', '/templates/create', '/templates/1', '/bot'] as $page) {
            $this->get($page)->assertOk();
        }
        $this->get('/notices/'.NoticeLevels::ACTIVE)->assertNotFound();
    }

    public function test_bot_settings_are_owner_only(): void
    {
        $this->actingAs($this->staff(owner: false));
        $this->get('/bot')->assertForbidden();
    }

    public function test_swap_tags_fill_in_the_rental(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Jane'));
        $rental->update(['expire_unixtime' => time() + 50 * 3600 + 1800]);
        $text = Swaps::forRental("Hi [[AVATAR_FIRSTNAME]] [[AVATAR_LASTNAME]]\r\n,[[NL]]port [[STREAM_PORT]] on [[SERVER_DOMAIN]], [[RENTAL_TIMELEFT]] left", $rental);

        $this->assertSame("Hi Jane Resident,\nport {$rental->stream->port} on stream.test, 2 days, 2 hours left", $text);
    }

    public function test_templates_create_update_and_delete(): void
    {
        Livewire::test(Templates\Edit::class)
            ->set('name', 'Radio template')->set('detail', 'Port [[STREAM_PORT]]')->set('notecard_detail', 'Port [[STREAM_PORT]] notecard')
            ->call('save')->assertHasNoErrors();
        $template = Template::where('name', 'Radio template')->firstOrFail();

        Livewire::test(Templates\Edit::class, ['template' => $template])
            ->assertSee('Port 4000')
            ->set('detail', 'Changed [[STREAM_PORT]]')->call('save')->assertHasNoErrors();
        $this->assertSame('Changed [[STREAM_PORT]]', $template->refresh()->detail);

        Livewire::test(Templates\Edit::class, ['template' => $template])->call('delete');
        $this->assertModelMissing($template);
    }

    public function test_a_template_used_by_a_package_is_not_removed(): void
    {
        $this->rental(); // creates a package on template 1
        Livewire::test(Templates\Edit::class, ['template' => Template::findOrFail(1)])->call('delete');
        $this->assertModelExists(Template::findOrFail(1));
        $this->assertTrue(Package::where('template_id', 1)->exists());
    }

    public function test_notice_hours_must_be_unique(): void
    {
        Livewire::test(Notices\Edit::class)
            ->set('name', 'Two day notice')->set('im_message', 'Two days left')->set('hours_remaining', 48)->call('save')
            ->assertHasNoErrors();
        Livewire::test(Notices\Edit::class)
            ->set('name', 'Another two day')->set('im_message', 'Two days left')->set('hours_remaining', 48)->call('save')
            ->assertHasErrors(['hours_remaining' => 'unique']);
    }

    public function test_the_expired_notice_keeps_zero_hours(): void
    {
        Livewire::test(Notices\Edit::class, ['notice' => Notice::findOrFail(NoticeLevels::EXPIRED)])
            ->set('name', 'Rental ended')->set('hours_remaining', 12)->call('save')->assertHasNoErrors();

        $expired = Notice::findOrFail(NoticeLevels::EXPIRED);
        $this->assertSame('Rental ended', $expired->name);
        $this->assertSame(0, $expired->hours_remaining);
    }

    public function test_removing_a_notice_moves_its_clients(): void
    {
        $rental = $this->rental();
        $rental->update(['notice_id' => 3]);
        RentalNoticeOptOut::create(['rental_id' => $rental->id, 'notice_id' => 3]);

        Livewire::test(Notices\Edit::class, ['notice' => Notice::findOrFail(3)])
            ->set('transferTo', 4)->call('delete')->assertHasNoErrors();

        $this->assertNull(Notice::find(3));
        $this->assertSame(4, $rental->refresh()->notice_id);
        $this->assertFalse(RentalNoticeOptOut::where('rental_id', $rental->id)->exists());
    }

    public function test_bulk_message_skips_banned_and_unticked_avatars(): void
    {
        BotConfig::whereKey(1)->update(['ims' => true]);
        $jane = $this->rental(avatar: $this->avatar('Jane Doe'));
        $this->rental(avatar: $jane->avatar); // second rental, same avatar: one message
        $skipped = $this->rental(avatar: $this->avatar('Skip Me'));
        $banned = $this->rental(avatar: $this->avatar('Banned Avatar'));
        Ban::create(['avatar_id' => $banned->avatar_id]);

        Livewire::test(Outbox\Compose::class)
            ->assertSet('selected', [(string) $jane->avatar_id, (string) $skipped->avatar_id])
            ->set('selected', [(string) $jane->avatar_id])
            ->set('message', 'Hello [[AVATAR_FIRSTNAME]], port [[STREAM_PORT]]')
            ->call('send')->assertHasNoErrors()->assertRedirect(route('outbox.index'));

        $this->assertSame(1, Message::count());
        $this->assertSame('Hello Jane, port '.$jane->stream->port, Message::first()->message);
        $im = BotCommand::firstOrFail();
        $this->assertSame('IM', $im->command);
        $this->assertSame([$jane->avatar->avatar_uuid, 'Hello Jane, port '.$jane->stream->port], json_decode($im->args, true));
    }

    public function test_bulk_message_by_package_and_single_client(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Only One'));

        Livewire::test(Outbox\Compose::class, ['source' => 'rental', 'sourceId' => $rental->id])
            ->assertSet('selected', [(string) $rental->avatar_id])
            ->set('message', 'Just for you [[AVATAR_FULLNAME]]')->call('send')->assertHasNoErrors();

        $this->assertSame('Just for you Only One', Message::first()->message);
        $this->assertSame(0, BotCommand::count(), 'bot IMs are off');
    }

    public function test_bot_settings_save(): void
    {
        $bot = $this->avatar('Helper Bot');
        Livewire::test(Bot\Settings::class)
            ->set('avatar_uid', $bot->avatar_uid)->set('secret', 'a-long-secret')->set('ims', true)
            ->set('invites', true)->set('invite_group_uuid', '')->call('save')
            ->assertHasErrors(['invite_group_uuid' => 'required'])
            ->set('invite_group_uuid', '9b8a2c4e-0f1d-4c3b-8a7e-6d5c4b3a2f10')->call('save')
            ->assertHasNoErrors();

        $config = BotConfig::findOrFail(1);
        $this->assertSame($bot->id, $config->avatar_id);
        $this->assertTrue($config->ims);
        $this->assertSame('a-long-secret', $config->secret);
    }

    public function test_bot_avatar_is_found_by_name(): void
    {
        $bot = $this->avatar('Helper Bot');
        $this->avatar('Helper Other');

        Livewire::test(Bot\Settings::class)
            ->call('clearAvatar')
            ->set('avatarSearch', 'Helper Bo')
            ->assertSee('Helper Bot')->assertDontSee('Helper Other')
            ->call('pickAvatar', $bot->avatar_uid)
            ->assertSet('avatar_uid', $bot->avatar_uid)
            ->set('secret', 'a-long-secret')->call('save')->assertHasNoErrors();

        $this->assertSame($bot->id, BotConfig::findOrFail(1)->avatar_id);
    }

    public function test_bot_http_test(): void
    {
        BotConfig::whereKey(1)->update(['http_mode' => true, 'http_url' => 'https://bot.test/', 'secret' => 'botsecret1']);
        Http::fake(['bot.test/*' => Http::response(['Key' => true, 'Value' => json_encode(['status' => true, 'reply' => 'world'])])]);

        Livewire::test(Bot\Settings::class)->call('testHttp');

        Http::assertSent(fn ($request) => $request->url() === 'https://bot.test/api/Run'
            && $request['commandName'] === 'Hello'
            && $request['signing'] === hash_hmac('sha256', "Hello\n\n".$request['unixtime'], 'botsecret1'));
    }
}
