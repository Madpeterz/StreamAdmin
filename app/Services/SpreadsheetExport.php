<?php

namespace App\Services;

use App\Models\Rental;
use App\Models\Stream;
use Illuminate\Support\Facades\File;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Options;
use OpenSpout\Writer\XLSX\Properties;
use OpenSpout\Writer\XLSX\Writer;

/**
 * The "flow1" stream spreadsheet (port of 2.0.x Export/Flow1): one row per stream with its client,
 * logins and expiry, as a snapshot outside the database. Column headers are unchanged from 2.0.x
 * so sheets that read the export keep working.
 */
class SpreadsheetExport
{
    public const HEADERS = ['ID', 'Customer Name', 'Port Number', 'Stream Password', 'Admin Username', 'Admin Password',
        'URL', 'Package Name', 'Comments', 'Expires at'];

    public function filename(): string
    {
        return now()->format('YmdHis').'-streamadmin.xlsx';
    }

    public function write(string $path): void
    {
        $writer = new Writer(new Options(properties: new Properties(
            title: 'StreamAdmin data',
            subject: 'A snapshot of non SQL data',
            application: 'StreamAdmin',
            creator: 'StreamAdmin',
            lastModifiedBy: 'StreamAdmin',
            keywords: 'streamadmin streams clients ports',
        )));
        $writer->openToFile($path);
        $writer->getCurrentSheet()->setName('Data');
        $writer->addRow(Row::fromValues(self::HEADERS));
        foreach ($this->rows() as $row) {
            $writer->addRow(Row::fromValues($row));
        }
        $writer->close();
    }

    /**
     * Writes a new export into $directory and deletes the oldest ones beyond $keep.
     *
     * @return string the new file
     */
    public function rotate(string $directory, int $keep): string
    {
        File::ensureDirectoryExists($directory);
        $path = rtrim($directory, '/\\').DIRECTORY_SEPARATOR.$this->filename();
        $this->write($path);
        $files = collect(File::glob(rtrim($directory, '/\\').'/*-streamadmin.xlsx'))->sort()->values();
        $files->take(max(0, $files->count() - max(1, $keep)))->each(fn (string $old) => File::delete($old));

        return $path;
    }

    /** @return iterable<int, list<int|string>> */
    private function rows(): iterable
    {
        $rentals = Rental::with('avatar')->get()->keyBy('stream_id');
        foreach (Stream::with(['server', 'package'])->orderBy('id')->lazy() as $stream) {
            $client = $stream->need_work ? '- Needs work -' : 'Available';
            $comments = 'None';
            $expires = '';
            $rental = $rentals->get($stream->id);
            if ($rental !== null) {
                $client = $rental->avatar->avatar_name ?? '-Taken-';
                $comments = (string) $rental->message !== '' ? (string) $rental->message : 'None';
                $expires = $rental->expire_unixtime?->isFuture() ? $rental->expire_unixtime->format('d/m/Y @ G:i:s') : 'Expired';
            }
            yield [
                $stream->id,
                html_entity_decode($client, ENT_QUOTES | ENT_HTML401),
                $stream->port,
                (string) $stream->dj_password,
                (string) $stream->admin_username,
                (string) $stream->admin_password,
                ($stream->server->domain ?? '').':'.$stream->port,
                $stream->package->name ?? '????',
                $comments,
                $expires,
            ];
        }
    }
}
