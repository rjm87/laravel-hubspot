<?php
return [
    'access_token' => env('HUBSPOT_ACCESS_TOKEN'),

    'contacts' => [
        'include_properties' => ['firstname','lastname','email'],
        'include_associations' => ['companies','deals','tickets'],
    ],

    'companies' => [
        'include_properties' => ['domain','name','phone'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'deals' => [
        'include_properties' => ['dealname','amount','closedate','pipeline','dealstage'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'products' => [
        'include_properties' => ['name','description','price'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'tickets' => [
        'include_properties' => ['content','subject'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'line_items' => [
        'include_properties' => ['quantity','amount','price'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'quotes' => [
        'include_properties' => [],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'calls' => [
        'include_properties' => ['hs_call_title','hubspot_owner_id','hs_call_body','hs_call_direction','hs_call_callee_object_id','hs_call_callee_object_type_id','hs_call_disposition','hs_call_duration','hs_call_from_number','hs_call_to_number'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'emails' => [
        'include_properties' => ['hubspot_owner_id','hs_timestamp','hs_email_subject','hs_email_status','hs_email_text','hs_email_direction'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ],

    'notes' => [
        'include_properties' => ['hubspot_owner_id','hs_timestamp','hs_note_body'],
        'include_associations' => ['companies','contacts','deals','tickets'],
    ]
];