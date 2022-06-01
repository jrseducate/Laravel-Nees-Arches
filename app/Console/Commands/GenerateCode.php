<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Code';

    /**
     * The colors listed by name
     *
     * @var array
     */
    protected $colors = [
        'lavender' => '#8a88ff',
        'blue' => '#5454ff',
        'light-blue' => '#c1c9ff',
        'white' => '#ddd',
        'gray' => '#888',
    ];

    /**
     * The styles to generate
     *
     * @var array
     */
    protected $styles = [
        '.na-navigation' => [
            'background-color' => '@blue',
        ],
        '.na-navigation-logo' => [],
        '.na-navigation-items' => [],
        '.na-navigation-item' => [
            'background-color' => '@light-blue',
            'color' => '#000',
        ],
        '.na-navigation-item.na-selected' => [
            'background' => 'linear-gradient(0deg, @lavender, @light-blue)',
            'border-bottom-color' => '@lavender',
            'color' => '#000',
        ],
        '.na-navigation-item:not(.na-selected):hover' => [
            'background-color' => '@lavender',
            'color' => '#fff',
        ],

        '.na-bg-lavender' => [
            'background-color' => '@lavender',
            'color' => '#fff',
        ],
        '.na-bg-blue' => [
            'background-color' => '@blue',
            'color' => '#fff',
        ],
        '.na-bg-white' => [
            'background-color' => '@white',
            'color' => '#000',
        ],
        '.na-bg-gray' => [
            'background-color' => '@gray',
            'color' => '#fff',
        ],

        '.na-font-bold' => [
            'font-weight' => '1000',
        ],

        '.na-border-bottom' => [
            'border-bottom-width' => '1px',
        ],

        '.na-border-black' => [
            'border-color' => '#000',
        ],
    ];

    /**
     * Generates the CSS Background Colors
     */
    public function generate_css()
    {
        $file = public_path("css/na-generated.css");

        file_put_contents($file, "");

        $handle = fopen($file, 'w');

        $keys = array_keys($this->styles);

        for($i = 0; $i < count($this->styles); $i++)
        {
            $name = $keys[$i];

            $rules = $this->styles[$name];
            $ruleKeys = array_keys($rules);

            $result = "$name {";

            for($j = 0; $j < count($ruleKeys); $j++)
            {
                $rule = $ruleKeys[$j];
                $value = $rules[$rule];

                $colorKeys = array_keys($this->colors);
                for($k = 0; $k < count($colorKeys); $k++)
                    $value = str_replace('@' . $colorKeys[$k], $this->colors[$colorKeys[$k]], $value);

                $result .= "--$rule: $value; $rule: var(--$rule);";
            }

            $result .= "} \n";

            fwrite($handle, $result);
        }

        fclose($handle);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->generate_css();

        return 1;
    }
}
