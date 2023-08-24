<?php

namespace Sapi\Format;

class FileFormat
{
    private static $mimeTypes = [
        'text/plain' => 'txt',
        'text/html' => 'html',
        'text/css' => 'css',
        'text/javascript' => 'js',
        'text/xml' => 'xml',
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/svg+xml' => 'svg',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
        'audio/ogg' => 'ogg',
        'audio/midi' => 'midi',
        'audio/aac' => 'aac',
        'video/mp4' => 'mp4',
        'video/mpeg' => 'mpeg',
        'video/quicktime' => 'mov',
        'video/webm' => 'webm',
        'video/x-msvideo' => 'avi',
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/json' => 'json',
        'application/xml' => 'xml',
        'application/octet-stream' => 'bin',
        'message/rfc822' => 'eml',
        'message/http' => 'http',
        'message/partial' => 'partial',
        'message/delivery-status' => 'dsn',
        'message/disposition-notification' => 'mdn',
    ];

    public function parse($val, $rule): bool
    {
        // TODO: Implement parse() method.
        if (!isset(self::$mimeTypes[$val['type']])) {
            return false;
        }

        if (!isset($rule['ext'])) {
            return true;
        }

        if (is_string($rule['ext'])) {
            $ext = explode(",", $rule['ext']);
        } else {
            $ext = $rule['ext'];
        }

        return in_array(self::$mimeTypes[$val['type']], $ext);
    }
}