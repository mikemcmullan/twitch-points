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
    'system' => [
        'misc' => [
            'uptime' => [
                'command' => '!uptime',
                'usage' => '!uptime',
                'description' => 'How long has the channel been live for.',
                'level' => 'everyone',
                'cool_down' => 5,
                'global_cool_down' => true,
                'module' => 'Uptime',
                'response' => '',
                'disabled' => false
            ],

            'howlong' => [
                'command' => 'regex:^!howlong(.*)$',
                'usage' => '!howlong [username]',
                'description' => 'How long has a user been following the channel.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'HowLong',
                'response' => '',
                'disabled' => false
            ],

            'russianroulette' => [
                'command' => '!rr',
                'usage' => '!rr',
                'description' => 'Russian Roulette. Everytime the command is run there is a 1 and 6 chance you will be timed out for 5 minutes.' .
                                 '  Once this happens the game cannot be played by anyone again for 5 minutes, a user can only play once every 30 seconds.',
                'level' => 'everyone',
                'cool_down' => 30,
                'global_cool_down' => false,
                'module' => 'RussianRoulette',
                'response' => 'Congratulations {{ user }}, you are our unlucky winner, see you again in 5 minutes.',
                'disabled' => false
            ],

            'silence' => [
                'command' => '!silence',
                'usage' => '!silence',
                'description' => 'Silence or unsilence the bot. This means it will not respond to any other commands or display timer messages.',
                'level' => 'admin',
                'cool_down' => 10,
                'global_cool_down' => true,
                'module' => 'Silence',
                'response' => '',
                'disabled' => false
            ],
        ],

        'commands' => [
            'add' => [
                'command' => '!commands add',
                'usage' => '!commands add <command> <text>',
                'description' => 'Add a new command. Example, <code>!command add !ping pong</code>',
                'level' => 'mod',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Commands/Add',
                'response' => '',
                'disabled' => false
            ],

            'edit' => [
                'command' => '!commands edit',
                'usage' => '!commands edit <command> <text>',
                'description' => 'Edit an existing command. Example, <code>!command edit !ping pong2</code>',
                'level' => 'mod',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Commands/Edit',
                'response' => '',
                'disabled' => false
            ],

            'delete' => [
                'command' => '!commands delete',
                'usage' => '!commands delete <command> <text>',
                'description' => 'Delete a command. Example, <code>!command delete !ping</code>',
                'level' => 'mod',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Commands/Delete',
                'response' => '',
                'disabled' => false
            ],

            'all' => [
                'command' => '!commands',
                'usage' => '!commands',
                'description' => 'Provides a link to the commands page.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => true,
                'module' => 'Simple',
                'response' => '{{ user }}, you can view all available bot commands here: %s',
                'disabled' => false
            ],
        ],

        'currency' => [
            'get' => [
                'command' => 'regex:^%s(\s+?\w{1,25})?$',
                'usage' => '%s [username]',
                'description' => 'Get the amount of currency for the provided username or for the user who executed the command.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'GetCurrency',
                'response' => '{{ display_name }} has {{ points }} coins. Rank: {{ rank }} | {{ named_rank }}',
                'disabled' => false
            ],

            'add' => [
                'command' => 'regex:^!currency (add) (\w+) ([\d]{1,4})$',
                'usage' => '!currency add <username> <amount>',
                'description' => 'Add currency to a user.',
                'level' => 'admin',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'AlterCurrency',
                'response' => '{{ display_name }} has been awarded {{ amount }} coins.',
                'disabled' => false
            ],

            'remove' => [
                'command' => 'regex:^!currency (remove) (\w+) ([\d]{1,4})$',
                'usage' => '!currency remove <username> <amount>',
                'description' => 'Remove currency from a user.',
                'level' => 'admin',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'AlterCurrency',
                'response' => '{{ amount }} coins have been removed from {{ display_name }}.',
                'disabled' => false
            ],

            'give' => [
                'command' => 'regex:^!give (\w+) ([\d]{1,4})$',
                'usage' => '!give <username> <amount>',
                'description' => 'Give your own currency to another user.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'GiveCurrency',
                'response' => '{{ source_display_name }} has given {{ display_name }} {{ amount }} coins.',
                'disabled' => false
            ],
        ],

        'giveaway' => [
            'enter' => [
                'command' => 'regex:^%s(.*)$',
                'usage' => '%s <ticket-amount>',
                'description' => 'Enter the giveaway.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'Giveaway',
                'response' => '',
                'disabled' => false
            ]
        ],

        'betting' => [
            'open' => [
                'command' => 'regex:^!betting\s?open (\d{1,4}) (\d{1,4}) (.*,.*)',
                'usage' => '!betting open <min-bet> <max-bet> <option-1>,<option-2>,etc...',
                'description' => 'Open betting. Options must be provided as a comma separated list. Example, <code>!betting open 0 10 mike, bob, joe</code>',
                'level' => 'admin',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Betting/Open',
                'response' => '',
                'disabled' => false
            ],

            'close' => [
                'command' => 'regex:^!betting\s?close$',
                'usage' => '!betting close',
                'description' => 'Close betting.',
                'level' => 'admin',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Betting/Close',
                'response' => '',
                'disabled' => false
            ],

            'winner' => [
                'command' => 'regex:^!betting\s?winner (.*)$',
                'usage' => '!betting winner <option-number>',
                'description' => 'Select the winning option and award currency to the winners.',
                'level' => 'admin',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Betting/Winner',
                'response' => '',
                'disabled' => false
            ],

            'bet' => [
                'command' => 'regex:^!bet (\d{1,4}) (.*)$',
                'usage' => '!bet <amount> <option>',
                'description' => 'Place a bet.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'Betting/Bet',
                'response' => '',
                'disabled' => false
            ],

            'options' => [
                'command' => 'regex:^!betting\s?options$',
                'usage' => '!betting options',
                'description' => 'Get options for the current bet.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => true,
                'module' => 'Betting/Options',
                'response' => '',
                'disabled' => false
            ]
        ],

        'quotes' => [
            'random' => [
                'command' => 'regex:^!quote\s?$',
                'usage' => '!quote',
                'description' => 'Get a random quote.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'Quotes/Random',
                'response' => '',
                'disabled' => false
            ],

            'get' => [
                'command' => 'regex:^!quote (\d{1,6})$',
                'usage' => '!quote <id>',
                'description' => 'Get a quote by it\'s ID.',
                'level' => 'everyone',
                'cool_down' => 3,
                'global_cool_down' => false,
                'module' => 'Quotes/Get',
                'response' => '',
                'disabled' => false
            ],

            'add' => [
                'command' => 'regex:^!quote add (.*)',
                'usage' => '!quote add <text>',
                'description' => 'Add a quote.',
                'level' => 'mod',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Quotes/Add',
                'response' => '',
                'disabled' => false
            ],

            'edit' => [
                'command' => 'regex:^!quote edit (\d{1,6}) (.*)',
                'usage' => '!quote edit <id> <text>',
                'description' => 'Edit a quote.',
                'level' => 'mod',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Quotes/Edit',
                'response' => '',
                'disabled' => false
            ],

            'delete' => [
                'command' => 'regex:^!quote del(?:ete)? (\d{1,6})',
                'usage' => '!quote delete <id>',
                'description' => 'Delete a quote.',
                'level' => 'mod',
                'cool_down' => 0,
                'global_cool_down' => false,
                'module' => 'Quotes/Delete',
                'response' => '',
                'disabled' => false
            ]
        ]
    ]
];
