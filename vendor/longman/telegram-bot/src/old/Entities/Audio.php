<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Entities;

use Longman\TelegramBot\Exception\TelegramException;

class Audio extends Entity
{
    protected $file_id;
    protected $duration;
    protected $performer;
    protected $title;
    protected $mime_type;
    protected $file_size;

    /**
     * Audio constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->file_id = isset($data['file_id']) ? $data['file_id'] : null;
        if (empty($this->file_id)) {
            throw new TelegramException('file_id is empty!');
        }

        $this->duration = isset($data['duration']) ? $data['duration'] : null;
        if ($this->duration === '' || $this->duration === null) {
            throw new TelegramException('duration is empty!');
        }

        $this->performer = isset($data['performer']) ? $data['performer'] : null;

        $this->title = isset($data['title']) ? $data['title'] : null;
        $this->mime_type = isset($data['mime_type']) ? $data['mime_type'] : null;
        $this->file_size = isset($data['file_size']) ? $data['file_size'] : null;
    }

    public function getFileId()
    {
        return $this->file_id;
    }

    public function getDuration()
    {
         return $this->duration;
    }

    public function getPerformer()
    {
        return $this->performer;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getMimeType()
    {
         return $this->mime_type;
    }

    public function getFileSize()
    {
         return $this->file_size;
    }
}
