<?php

namespace Tests\Feature\Api;

use App\Api\ApiClass;
use App\Models\Notecard;
use App\Models\Notice;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesStreamAdminData;
use Tests\Support\SignsApiRequests;
use Tests\TestCase;

/** The bot writes dynamic notecards from the queue. */
class BotApiTest extends TestCase
{
    use CreatesStreamAdminData;
    use RefreshDatabase;
    use SignsApiRequests;

    private function next()
    {
        return $this->api('api/v3/bot/notecards/next', class: ApiClass::Bot);
    }

    public function test_nothing_waiting(): void
    {
        $this->next()->assertOk()->assertExactJson(['ok' => true, 'message' => 'nowork', 'data' => ['notecard' => null]]);
    }

    public function test_a_rentals_details_notecard(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Jane Doe'));
        Template::findOrFail(1)->update(['notecard_detail' => 'Hello [[AVATAR_FIRSTNAME]] port [[STREAM_PORT]]']);
        Notecard::create(['rental_id' => $rental->id, 'as_notice' => false]);

        $response = $this->next()->assertOk()->assertJson(['message' => 'ok'])
            ->assertJsonPath('data.notecard.avatarKey', $rental->avatar->avatar_uuid)
            ->assertJsonPath('data.notecard.content', 'Hello Jane port '.$rental->stream->port);
        $this->assertStringStartsWith('Details for Jane port: '.$rental->stream->port.' [', $response->json('data.notecard.title'));
        $this->assertSame(0, Notecard::count());
    }

    public function test_a_notices_notecard(): void
    {
        $rental = $this->rental(avatar: $this->avatar('Jane Doe'));
        Notice::findOrFail(4)->update(['notecard_detail' => 'Renew port [[STREAM_PORT]]']);
        Notecard::create(['rental_id' => $rental->id, 'as_notice' => true, 'notice_id' => 4]);

        $this->next()->assertOk()
            ->assertJsonPath('data.notecard.title', 'Reminder for Jane Doe port: '.$rental->stream->port)
            ->assertJsonPath('data.notecard.content', 'Renew port '.$rental->stream->port);
    }

    public function test_an_empty_template_fails_and_keeps_the_notecard_queued(): void
    {
        $rental = $this->rental();
        Template::findOrFail(1)->update(['notecard_detail' => '']);
        Notecard::create(['rental_id' => $rental->id, 'as_notice' => false]);

        $this->next()->assertStatus(422)->assertJson(['message' => 'The selected template (Shoutcast) has no notecard text']);

        $this->assertSame(1, Notecard::count());
    }

    public function test_only_the_bot_key_may_ask(): void
    {
        $this->api('api/v3/bot/notecards/next', class: ApiClass::Owner)->assertStatus(403);
        $this->api('api/v3/bot/notecards/next', class: ApiClass::Vendor)->assertStatus(403);
    }
}
