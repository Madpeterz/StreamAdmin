<?php

namespace App\Livewire\Notices;

use App\Models\Notice;
use App\Models\NoticeNotecard;
use App\Models\RentalNoticeOptOut;
use App\Services\MessagingService;
use App\Services\NoticeLevels;
use App\Support\Swaps;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Component;
use RuntimeException;

/**
 * Create (no notice) or edit a notice level. Rules follow the 2.0.x Notice controls: the Active
 * level cannot be edited, and the Expired level always triggers at 0 hours.
 */
class Edit extends Component
{
    public ?Notice $notice = null;

    public string $name = '';

    public string $im_message = '';

    public bool $send_object_im = true;

    public bool $use_bot = false;

    public ?int $hours_remaining = null;

    public bool $send_notecard = false;

    public string $notecard_detail = '';

    public int $notice_notecard_id = 1;

    public ?int $transferTo = null;

    public function mount(?Notice $notice = null): void
    {
        if ($notice?->exists) {
            abort_if($notice->id === NoticeLevels::ACTIVE, 404);
            $this->notice = $notice;
            $this->fill($notice->only([
                'name', 'im_message', 'send_object_im', 'use_bot', 'hours_remaining', 'send_notecard', 'notecard_detail', 'notice_notecard_id',
            ]));
            $this->notecard_detail = trim($this->notecard_detail);
        }
    }

    public function isExpiredLevel(): bool
    {
        return $this->notice?->id === NoticeLevels::EXPIRED;
    }

    public function save(MessagingService $messaging): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'min:5', 'max:100'],
            'im_message' => ['required', 'string', 'min:5', 'max:800'],
            'send_object_im' => ['boolean'],
            'use_bot' => ['boolean'],
            'hours_remaining' => $this->isExpiredLevel() ? [] : [
                'required', 'integer', 'min:1', 'max:998',
                Rule::unique('notices', 'hours_remaining')->ignore($this->notice?->id),
            ],
            'send_notecard' => ['boolean'],
            'notecard_detail' => [$this->send_notecard ? 'required' : 'nullable', 'string', 'max:2000'],
            'notice_notecard_id' => ['required', Rule::exists('notice_notecards', 'id')->where('missing', false)],
        ], [
            'hours_remaining.unique' => 'There is already a notice at that many hours.',
            'notice_notecard_id.exists' => 'That static notecard is missing from the notecard server; choose another.',
        ], ['im_message' => 'message', 'hours_remaining' => 'hours remaining', 'notecard_detail' => 'notecard']);
        $data['notecard_detail'] ??= '';
        if ($this->isExpiredLevel()) {
            unset($data['hours_remaining']);
        }
        $creating = $this->notice === null;
        $this->notice = $messaging->saveNotice($this->notice, $data);
        Flux::toast(variant: 'success', text: $creating ? 'Notice created.' : 'Notice saved.');
        if ($creating) {
            $this->redirectRoute('notices.edit', $this->notice, navigate: true);
        }
    }

    public function delete(MessagingService $messaging): void
    {
        $this->validate(['transferTo' => ['required', 'exists:notices,id']], [], ['transferTo' => 'notice level']);
        try {
            $moved = $messaging->deleteNotice($this->notice, Notice::findOrFail($this->transferTo));
        } catch (RuntimeException $e) {
            $this->addError('transferTo', $e->getMessage());

            return;
        }
        Flux::toast(variant: 'success', text: $moved > 0 ? "Notice removed; {$moved} ".str('client')->plural($moved).' moved.' : 'Notice removed.');
        $this->redirectRoute('notices.index', navigate: true);
    }

    public function render()
    {
        $missing = $this->notice?->noticeNotecard?->missing ?? false;

        return view('livewire.notices.edit', [
            'notecards' => NoticeNotecard::where('missing', false)->orderBy('name')->get(),
            'currentNotecardMissing' => $missing,
            'previewIm' => Swaps::example($this->im_message),
            'others' => Notice::whereKeyNot($this->notice?->id)->orderByDesc('hours_remaining')->get(),
            'clientCount' => $this->notice?->rentals()->count() ?? 0,
            'optOutCount' => $this->notice ? RentalNoticeOptOut::where('notice_id', $this->notice->id)->count() : 0,
        ])->title($this->notice?->name ?? 'New notice');
    }
}
