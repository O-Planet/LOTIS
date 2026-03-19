<?php
namespace LTS;

class Lang extends Construct
{
    private $elements;
    public $words = [];
    private $loaded = false;

    function __construct($id = '')
    {
        parent::__construct($id);
        $this->elements = array();
        $this->type = 'html';
        $this->id = $id;

        if ($id !== '') {
            $currentFile = $_SERVER['SCRIPT_FILENAME'];
            $fileNameWithExt = basename($currentFile);
            $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

            // Путь к файлу: например, "about.ru"
            $langFilePath = "{$fileName}.{$id}";

            // Загружаем файл перевода
            $this->loaded = $this->loadLangFile($langFilePath);
        }
    }

    public function say($word, $key = false)
    {
        if ($this->loaded) {
            $_key = $key === false ? $word : $key;
            if (array_key_exists($_key, $this->words)) {
                return $this->words[$_key];
            }
        }

        $_key = $key === false ? $word : $key;
        if(! array_key_exists($_key, $this->words))
            $this->words[$_key] = $word;

        return $word;
    }

    // Приватный метод для загрузки языкового файла
    private function loadLangFile($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lang = [];

        $currentKey = null;

        foreach ($lines as $line) {
            // Если строка начинается не с пробела — это новый ключ
            if (preg_match('/^\S/', $line)) 
            {
                $parts = explode(':', $line, 2);
                if (count($parts) === 2) 
                {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $lang[$key] = $value;
                    $currentKey = $key;
                } 
                else 
                    $currentKey = null;
            } 
            else 
                // Продолжение предыдущего значения
                if ($currentKey !== null) 
                {
                    $value = $lang[$currentKey];
                    $value .= "\n" . ltrim($line); // добавляем новую строку
                    $lang[$currentKey] = $value;
                }
        }

        $this->words = $lang;
        return true;
    }

        // Сохранение словаря в файл
    public function save($lng)
    {
        $currentFile = $_SERVER['SCRIPT_FILENAME'];
        $fileNameWithExt = basename($currentFile);
        $fileName = pathinfo($fileNameWithExt, PATHINFO_FILENAME);

        $filePath = $fileName . '.' . $lng;

        $content = '';
        foreach ($this->words as $key => $value) {
            $lines = explode("\n", $value);
            $firstLine = $key . ':' . $lines[0];
            $content .= $firstLine . "\n";

            for ($i = 1; $i < count($lines); $i++) {
                $content .= "    " . $lines[$i] . "\n"; // добавляем отступ
            }
        }

        file_put_contents($filePath, $content);

        return $this;
    }

    public function shine($parent = null) 
    {
        if($this->loaded)
        {
            $strwords = json_encode($this->words);
            $this->elements['script'] = array('type' => 'JS', 
                'area' => 'script', 
                'id' => 0, 
                'body' => "LTS.lang('{$this->id}', {$strwords});", 
                'on' => false); 
        }        
    }

    public function addinspace()
    {
        if($this->loaded)
            $this->space->set('elements', "{$this->id}", $this->elements['script']);
    }
}
?>
