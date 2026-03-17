<?php
header('Content-Type: application/json');
$agencies = json_decode(Tools::file_get_contents('./agencies.json'));


function getById($agencies, $id)
{
    return array_filter($agencies, function ($item) use ($id) {
        return $item->agency_id == $id;
    });
}

function getAll($agencies)
{
    $results = [];
    foreach ($agencies as $value) {
        $results[] = ["id" => $value->agency_id, "text" => $value->agency_name];
    }
    return $results;
}

$data = Tools::getIsset('id') ? getById($agencies, Tools::getValue('id')) : getAll($agencies);

echo json_encode($data);
