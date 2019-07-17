<?php

namespace ofilin\quill;

use yii\web\AssetBundle;

class EmojiAsset extends AssetBundle
{

    public $sourcePath = '@ofilin/quill/src/emoji';

    public $css = [
        'quill-emoji.css',
    ];

    public $js = [
        'quill-emoji.js',
    ];
}
