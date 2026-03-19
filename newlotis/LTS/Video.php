<?php
namespace LTS;

class Video extends Element
{
    /**
     * Конструктор
     * Создает тег <video>
     */
    public function __construct($id = '')
    {
        parent::__construct($id);
        $this->tagname = 'video';
    }

    /**
     * Добавляет источник медиафайла.
     * Автоматически определяет MIME-тип по расширению файла.
     *
     * @param string $src URL к файлу
     * @return $this Возвращает сам объект для цепочки вызовов
     */
    public function src($src)
    {
        $source = new Element();
        $source->tag('source');
        $source->attr('src', $src);

        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'mp3':
                $source->attr('type', 'audio/mpeg');
                break;
            case 'mp4':
                $source->attr('type', 'video/mp4; codecs="avc1.42E01E, mp4a.40.2"');
                break;
            case 'webm':
                $source->attr('type', 'video/webm; codecs="vp8, vorbis"');
                break;
            case 'ogv':
                $source->attr('type', 'video/ogg; codecs="theora, vorbis"');
                break;
        }
        // Для других форматов type можно не указывать

        $this->add($source);
        return $this;
    }

    /**
     * Поддержка нескольких источников
     */
    public function sources($sources)
    {
        foreach ($sources as $src) {
            $this->src($src);
        }
        return $this;
    }

    /**
     * Включает автовоспроизведение
     */
    public function autoplay()
    {
        $this->attr('autoplay', 'autoplay');
        return $this;
    }

    /**
     * Включает предзагрузку видео
     */
    public function preload()
    {
        $this->attr('preload', 'auto');
        return $this;
    }

    /**
     * Отображает элементы управления
     */
    public function controls()
    {
        $this->attr('controls', 'controls');
        return $this;
    }

    /**
     * Включает бесконечное воспроизведение
     */
    public function loop()
    {
        $this->attr('loop', 'loop');
        return $this;
    }

    /**
     * Устанавливает изображение-превью (постер)
     */
    public function poster($src = null)
    {
        $this->attr('poster', $src);
        return $this;
    }

    /**
     * Устанавливает ширину видео
     */
    public function width($val = null)
    {
        $this->attr('width', $val);
        return $this;
    }

    /**
     * Устанавливает высоту видео
     */
    public function height($val = null)
    {
        $this->attr('height', $val);
        return $this;
    }

    /*
    * Управление звуком по умолчанию
    */
    public function muted()
    {
        $this->attr('muted', 'muted');
        return $this;
    }

    /*
    * Для мобильных устройств — воспроизведение без перехода в полноэкранный режим
    */
    public function playsinline()
    {
        $this->attr('playsinline', 'playsinline');
        return $this;
    }

    /*
    * Для загрузки видео с других доменов
    */
    public function crossorigin($value = 'anonymous')
    {
        $this->attr('crossorigin', $value);
        return $this;
    }

    /*
    * Отключение режима «картинка в картинке»
    */
    public function disablepictureinpicture()
    {
        $this->attr('disablepictureinpicture', 'true');
        return $this;
    }
}
?>