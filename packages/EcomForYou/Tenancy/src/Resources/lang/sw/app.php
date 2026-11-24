<?php

return [
    'errors' => [
        'not-found' => [
            'title' => 'Duka halijapatikana',
            'message' => 'Hakuna duka kwenye anwani hii. Kagua anwani na ujaribu tena.',
        ],

        'suspended' => [
            'title' => 'Duka halipatikani',
            'message' => 'Duka hili halipatikani kwa sasa. Tafadhali rudi baadaye.',
        ],

        'provisioning' => [
            'title' => 'Duka linaandaliwa',
            'message' => 'Duka hili linaandaliwa na litakuwa tayari baada ya muda mfupi. Tafadhali onyesha upya ukurasa baada ya muda mfupi.',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => ':attribute lazima iwe na herufi ndogo, nambari au vistari 3 hadi 63, na haiwezi kuanza wala kuishia na kistari.',
            'reserved' => ':attribute imehifadhiwa na haiwezi kutumika kwa duka.',
            'taken' => ':attribute tayari imechukuliwa na duka lingine.',
        ],
    ],
];
