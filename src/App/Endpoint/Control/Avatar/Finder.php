<?php

namespace App\Endpoint\Control\Avatar;

use App\Models\AvatarSet;
use App\Template\ViewAjax;
use YAPF\InputFilter\InputFilter;

class Finder extends ViewAjax
{
    public function process(): void
    {
        $this->setSwapTag("status", "true");

        $input = new inputFilter();
        $avatarfindname = $input->postFilter("avatarfind");
        $where_config = [
            "fields" => ["avatarUUID","avatarName","avatarUid"],
            "matches" => ["% LIKE %","% LIKE %","% LIKE %"],
            "values" => [$avatarfindname,$avatarfindname,$avatarfindname],
            "types" => ["s","s","s"],
            "join_with" => ["OR","OR"],
        ];
        $search_avatar_set = new AvatarSet();
        $search_avatar_set->loadWithConfig($where_config);
        $scored_results = [];
        if ($search_avatar_set->getCount() == 0) {
            $this->setSwapTag("message", "nope");
            return;
        }
        foreach ($search_avatar_set->getAllIds() as $result_id) {
            $avatar = $search_avatar_set->getObjectByID($result_id);
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
        $this->setSwapTagArray("values", []);
        if (count($scored_results) > 0) {
            $this->setSwapTagArray("values", $scored_results[0]);
        }
        $this->setSwapTag("message", "ok");
    }
}
