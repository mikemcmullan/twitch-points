<?php

namespace App\Bot;

use App\Exceptions\FileInaccessibleException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class LogParser
{
    /**
     * Make sure the file exists then fetch it.
     *
     * @param $file
     *
     * @return array
     * @throws FileNotFoundException
     * @throws FileInaccessibleException
     */
    private function load($file)
    {
        if (! file_exists($file)) {
            throw new FileNotFoundException("Bot log file ({$file}) does not exist.");
        }

        $content = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (! $content && ! is_array($content)) {
            throw new FileInaccessibleException("Something when wrong getting the bot log file ({$file}).");
        }

        return $content;
    }

    /**
     * Parse the file content.
     *
     * @param array $content
     * @param int $offset
     *
     * @return array
     */
    private function parse(array $content, $offset = 0)
    {
        $content = array_slice($content, $offset);
        $content = array_map('e', $content);
        $content = array_map(function ($entry) {
            $entry = preg_replace('/[^[:print:]]/', '', $entry);
            $entry = preg_replace('/\[\d{2}m(\w+)\[\d{2}m/', '<span class="bot-log-entry-$1">$1</span>', $entry);

            return $entry;
        }, $content);

        return $content;
    }

    /**
     * @param $file
     * @param $offset
     *
     * @return array
     * @throws FileNotFoundException
     * @throws \Exception
     */
    public function getLog($file, $offset)
    {
        $content = $this->load($file);

        return $this->parse($content, $offset);
    }
}
