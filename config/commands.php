<?php

/*
|--------------------------------------------------------------------------
| Commands
|--------------------------------------------------------------------------
|
| List of default bot commands.
|
*/
return [
    'default' => [
        'currency' => [
            'get' => [
                'command' => 'regex:^%s(\s+?\w{1,25})?$',
                'usage' => '%s [username]',
                'description' => 'Get the amount of currency for the provided username or for the user who executed the command.',
                'level' => 'everyone',
                'module' => 'GetCurrency',
                'response' => '{{ handle }} has {{ points }} coins. Rank: {{ rank }} | {{ named_rank }}'
            ],

            'add' => [
                'command' => 'regex:^!currency (add) (\w+) ([\d]{1,4})$',
                'usage' => '!currency add <username> <amount>',
                'description' => 'Add currency to a user.',
                'level' => 'admin',
                'module' => 'AlterCurrency',
                'response' => '{{ handle }} has been awarded {{ amount }} coins.'
            ],

            'remove' => [
                'command' => 'regex:^!currency (remove) (\w+) ([\d]{1,4})$',
                'usage' => '!currency remove <username> <amount>',
                'description' => 'Remove currency from a user.',
                'level' => 'admin',
                'module' => 'AlterCurrency',
                'response' => '{{ amount }} coins have been removed from {{ handle }}.'
            ],

            'give' => [
                'command' => 'regex:^!give (\w+) ([\d]{1,4})$',
                'usage' => '!give <username> <amount>',
                'description' => 'Give your own currency to another user.',
                'level' => 'everyone',
                'module' => 'GiveCurrency',
                'response' => 'Give your own currency to another user.'
            ]
        ],

        'giveaway' => [
            'enter' => [
                'command' => 'regex:^%s(.*)$',
                'usage' => '%s <ticket-amount>',
                'description' => 'Enter the giveaway.',
                'level' => 'everyone',
                'module' => 'Giveaway',
                'response' => ''
            ]
        ],

        'betting' => [
            'open' => [
                'command' => '^!betting\s?open (\d{1,4}) (\d{1,4}) (.*,.*)',
                'usage' => '!betting open <min-bet> <max-bet> <option-1>,<option-2>,etc...',
                'description' => 'Open betting. Options must be provided as a comma separated list. Example, <code>!betting open 0 10 mike, bob, joe</code>',
                'level' => 'admin',
                'module' => 'Betting/Open',
                'response' => ''
            ],

            'close' => [
                'command' => '^!betting\s?close$',
                'usage' => '!betting close',
                'description' => 'Close betting.',
                'level' => 'admin',
                'module' => 'Betting/Close',
                'response' => ''
            ],

            'winner' => [
                'command' => '^!betting\s?winner (\d{1,2})$',
                'usage' => '!betting winner <option-number>',
                'description' => 'Select the winning option and award currency to the winners.',
                'level' => 'admin',
                'module' => 'Betting/Winner',
                'response' => ''
            ],

            'bet' => [
                'command' => 'regex:^!bet (\d{1,4}) (.*)$',
                'usage' => '!bet <amount> <option>',
                'description' => 'Place a bet.',
                'level' => 'everyone',
                'module' => 'Betting/Bet',
                'response' => ''
            ],

            'options' => [
                'command' => '^!betting\s?options$',
                'usage' => '!betting options',
                'description' => 'Get options for the current bet.',
                'level' => 'everyone',
                'module' => 'Betting/Options',
                'response' => ''
            ]
        ],

        'quotes' => [
            'random' => [
                'command' => '!quote',
                'usage' => '!quote',
                'description' => 'Get a random quote.',
                'level' => 'everyone',
                'module' => 'Quotes/Random',
                'response' => ''
            ],

            'get' => [
                'command' => 'regex:^!quote (\d{1,6})$',
                'usage' => '!quote <id>',
                'description' => 'Get a quote by it\'s ID.',
                'level' => 'everyone',
                'module' => 'Quotes/Get',
                'response' => ''
            ],

            'add' => [
                'command' => 'regex:^!quote add (.*)',
                'usage' => '!quote add <text>',
                'description' => 'Add a quote.',
                'level' => 'mod',
                'module' => 'Quotes/Add',
                'response' => ''
            ],

            'edit' => [
                'command' => 'regex:^!quote edit (\d{1,6}) (.*)',
                'usage' => '!quote edit <id> <text>',
                'description' => 'Edit a quote.',
                'level' => 'mod',
                'module' => 'Quotes/Edit',
                'response' => ''
            ],

            'delete' => [
                'command' => 'regex:^!quote del(?:ete)? (\d{1,6})',
                'usage' => '!quote delete <id>',
                'description' => 'Delete a quote.',
                'level' => 'mod',
                'module' => 'Quotes/Delete',
                'response' => ''
            ]
        ],

        'misc' => [
            'uptime' => [
                'command' => '!uptime',
                'usage' => '!uptime',
                'description' => 'How long has the channel been live for.',
                'level' => 'everyone',
                'module' => 'Uptime',
                'response' => ''
            ],

            'commands' => [
                'command' => '!commands',
                'usage' => '!commands',
                'description' => 'Provides a link to the commands page.',
                'level' => 'everyone',
                'module' => '',
                'response' => '{{ user }}, you can view all available bot commands here: https://jonzzzzz.mnt.co/commands'
            ],

            'howlong' => [
                'command' => 'regex:^!howlong (\w{1,25})$',
                'usage' => '!howlong [username]',
                'description' => 'How long has a user been following the channel.',
                'level' => 'everyone',
                'module' => 'HowLong',
                'response' => ''
            ]
        ]
    ]
];
