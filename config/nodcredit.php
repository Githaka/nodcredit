<?php

return [

    'administrator' => 'admin@nodcredit.com',

    'mail_to_admins' => explode(',', env('MAIL_TO_ADMINS', 'admin@nodcredit.com,yomi@nodcredit.com')),

    'mail_to' => [
        'new_loans' => explode(',', env('NODCREDIT_NEW_LOANS_MAIL_TO', 'timchuks87@gmail.com')),
        'not_parsed_loans' => explode(',', env('NODCREDIT_NOT_PARSED_LOANS_MAIL_TO', 'timchuks87@gmail.com,Yomi@nodcredit.com')),
        'parsed_loans' => explode(',', env('NODCREDIT_PARSED_LOANS_MAIL_TO', 'timchuks87@gmail.com,abayomi.olofinlua@gmail.com')),
        'loan_approved' => explode(',', env('NODCREDIT_LOAN_APPROVED_MAIL_TO', 'timchuks87@gmail.com,Yomi@nodcredit.com')),
    ],

];
