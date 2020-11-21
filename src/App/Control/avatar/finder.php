<?php

$input = new inputFilter();
$avatarfindname = $input->postFilter("avatarfind");
$whereconfig = [];
$where_config = [
    "fields" => ["avataruuid","avatarname","avatar_uid"],
    "matches" => ["% LIKE %","% LIKE %","% LIKE %"],
    "values" => [$avatarfindname,$avatarfindname,$avatarfindname],
    "types" => ["s","s","s"],
    "join_with" => ["OR","OR"],
];
$search_avatar_set = new avatar_set();
$search_avatar_set->loadWithConfig($where_config);

$scored_results = [];
$status = true;
if ($search_avatar_set->getCount() > 0) {
    foreach ($search_avatar_set->getAllIds() as $result_id) {
        $avatar = $search_avatar_set->getObjectByID($result_id);
        $percent_uuid = 0;
        $percent_name = 0;
        similar_text($avatarfindname, $avatar->get_avataruuid(), $percent_uuid);
        similar_text($avatarfindname, $avatar->getAvatarname(), $percent_name);
        $match_score = $percent_name;
        if ($percent_uuid > $percent_name) {
            $match_score = $percent_uuid;
        }
        $scored_results[] = ["score" => round($match_score),"matchuid" => $avatar->getAvatar_uid(),"matchname" => $avatar->getAvatarname()];
    }
    usort($scored_results, function ($a, $b) {
        return $a['score'] <=> $b['score'];
    });
    $reply_values = [];
    if (count($scored_results) > 0) {
        $reply["values"] = $scored_results[0];
    }
    $ajax_reply->set_swap_tag_string("message", "ok");
} else {
    $ajax_reply->set_swap_tag_string("message", "nope");
}
