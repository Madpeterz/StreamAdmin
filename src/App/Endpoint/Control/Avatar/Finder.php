<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\Sets\AvatarSet;
use App\Template\ControlAjax;

class Finder extends ControlAjax
{
    public function process(): void
    {
        $this->setSwapTag("status", true);

        $avatarfindname = $this->input->post("avatarfind")->checkStringLengthMin(3)->asString();
        if ($avatarfindname == null) {
            $this->failed("Avatar UID/Name/UUID was not sent!");
            return;
        }
        $where_config = [
            "fields" => ["avatarUUID","avatarName","avatarUid"],
            "matches" => ["% LIKE %","% LIKE %","% LIKE %"],
            "values" => [$avatarfindname,$avatarfindname,$avatarfindname],
            "types" => ["s","s","s"],
            "joinWith" => ["OR","OR"],
        ];
        $search_avatar_set = new AvatarSet();
        $search_avatar_set->loadWithConfig($where_config);
        $scored_results = [];
        if ($search_avatar_set->getCount() == 0) {
            $this->failed("nope");
            return;
        }
        foreach ($search_avatar_set as $avatar) {
            $percent_uuid = 0;
            $percent_name = 0;
            similar_text($avatarfindname, $avatar->getAvatarUUID(), $percent_uuid);
            similar_text($avatarfindname, $avatar->getAvatarName(), $percent_name);
            $match_score = $percent_name;
            if ($percent_uuid > $percent_name) {
                $match_score = $percent_uuid;
            }
            $scored_results[] = [
                "score" => round($match_score),
                "matchuid" => $avatar->getAvatarUid(),
                "matchname" => $avatar->getAvatarName(),
            ];
        }
        usort($scored_results, function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });
        $this->output->setSwapTagArray("values", []);
        if (count($scored_results) > 0) {
            $this->output->setSwapTagArray("values", $scored_results[0]);
        }
        $this->ok("ok");
    }
}
