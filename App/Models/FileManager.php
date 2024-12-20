<?php

class FileManager {
    public static function read($file) {
        return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
    }

    public static function write($file, $data) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    public static function delete($file, $id) {
        $records = self::read($file);
        $records = array_filter($records, function ($record) use ($id) {
            return $record['id'] !== $id;
        });
        self::write($file, array_values($records));
    }
}
