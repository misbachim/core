<?php

$a = [
    [
        "id" => 9,
        "level" => "1",
        "positionCode" => "MGR-BPPM",
        "parentPositionCode" => null
    ],
    [
        "id" => 10,
        "level" => "2",
        "positionCode" => "STAFF-MD",
        "parentPositionCode" => "MGR-BPPM"
    ],
    [
        "id" => 11,
        "level" => "2",
        "positionCode" => "MGR-MO",
        "parentPositionCode" => "MGR-BPPM"
    ],
    [
        "id" => 12,
        "level" => "3",
        "positionCode" => "STAFF-PS",
        "parentPositionCode" => "STAFF-MD"
    ],
    [
        "id" => 13,
        "level" => "3",
        "positionCode" => "SPV-PHS",
        "parentPositionCode" => "STAFF-MD"
    ],
    [
        "id" => 14,
        "level" => "4",
        "positionCode" => "STAFF-SOES",
        "parentPositionCode" => "STAFF-PS"
    ],
    [
        "id" => 15,
        "level" => "4",
        "positionCode" => "STAFF-RS",
        "parentPositionCode" => "STAFF-PS"
    ]
];

$maxLevel = 4;
for ($i = $maxLevel; $i > 0; $i--) {
    for ($j = 0; $j < count($a); $j++) {
        if ($i === $a[$j]['level']) {
            echo $i;
        }
    }
}
// echo json_encode($a, JSON_PRETTY_PRINT);
?>
