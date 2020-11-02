<?php

$input = new inputFilter();
$avatarfindname = $input->postFilter("avatarfind");
$whereconfig = [];
$where_config = array(
    "fields" => array("avataruuid","avatarname","avatar_uid"),
    "matches" => array("% LIKE %","% LIKE %","% LIKE %"),
    "values" => array($avatarfindname,$avatarfindname,$avatarfindname),
    "types" => array("s","s","s"),
    "join_with" => array("OR","OR")
);
$search_avatar_set = new avatar_set();
$search_avatar_set->load_with_config($where_config);

$scored_results = [];
$status = true;
if ($search_avatar_set->get_count() > 0) {
    foreach ($search_avatar_set->get_all_ids() as $result_id) {
        $avatar = $search_avatar_set->get_object_by_id($result_id);
        $percent_uuid = 0;
        $percent_name = 0;
        similar_text($avatarfindname, $avatar->get_avataruuid(), $percent_uuid);
        similar_text($avatarfindname, $avatar->get_avatarname(), $percent_name);
        $match_score = $percent_name;
        if ($percent_uuid > $percent_name) {
            $match_score = $percent_uuid;
        }
        $scored_results[] = array("score" => round($match_score),"matchuid" => $avatar->get_avatar_uid(),"matchname" => $avatar->get_avatarname());
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
