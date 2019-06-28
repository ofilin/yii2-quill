<?php

namespace ofilin\quill;

use yii\web\AssetBundle;

class QuillAsset extends AssetBundle
{

    public $theme;

    public $sourcePath = '@ofilin/quill/src/dist';


    public function registerAssetFiles($view)
    {
        switch ($this->theme) {
            case Quill::THEME_SNOW:
                $this->css = ['quill.snow.css'];
                break;
            case Quill::THEME_BUBBLE:
                $this->css = ['quill.bubble.css'];
                break;
            default:
                $this->css = ['quill.core.css'];
        }

        $this->js = ['quill.min.js'];

        parent::registerAssetFiles($view);
    }
    
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
