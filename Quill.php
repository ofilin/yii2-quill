<?php

namespace ofilin\quill;

use Codeception\Module\EmulateModuleHelper;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * This is fork https://github.com/bizley/yii2-quill
 * Quill editor implementation for Yii 2.
 *
 * Use it as an active field:
 * <?= $form->field($model, $attribute)->widget(\ofilin\quill\Quill::class, []) ?>
 *
 * Or as a standalone widget:
 * <?= \ofilin\quill\Quill::widget(['name' => 'editor']) ?>
 *
 * See the documentation for more details.
 *
 * @author Paweł Bizley Brzozowski
 * @version 2.3.0
 * @license Apache 2.0
 * https://github.com/bizley/yii2-quill
 *
 * Quill itself can be found at
 * https://quilljs.com/
 * https://github.com/quilljs/quill/
 */
class Quill extends InputWidget
{
    const THEME_SNOW = 'snow';
    const THEME_BUBBLE = 'bubble';

    /**
     * @var string Theme to be set.
     * See https://quilljs.com/docs/themes/ for more info.
     * Set it to 'snow' [Quill::THEME_SNOW] to get snow theme.
     * Set it to 'bubble' [Quill::THEME_BUBBLE] to get bubble theme.
     * Set it to false or null to remove theme.
     * This property is skipped if $configuration is set.
     */
    public $theme = self::THEME_SNOW;

    const TOOLBAR_FULL = 'FULL';
    const TOOLBAR_BASIC = 'BASIC';

    /**
     * @var bool|string|array Toolbar buttons.
     * Set true to get theme default buttons.
     * You can use above constants for predefined set of buttons.
     * For other options see README and https://quilljs.com/docs/modules/toolbar/
     * @since 2.0
     */
    public $toolbarOptions = true;

    /**
     * @var string Placeholder text to be displayed in the editor field.
     * Leave empty for default value.
     * This property is skipped if $configuration is set.
     * @since 2.0
     */
    public $placeholder;

    /**
     * @var string DOM Element that editor ui elements, such as tooltips, should be confined within.
     * It will be automatically wrapped in JsExpression.
     * Leave empty for default value.
     * This property is skipped if $configuration is set.
     * @since 2.0
     */
    public $bounds;

    /**
     * @var string Static method enabling logging messages at a given level: 'error', 'warn', 'log', or 'info'.
     * Leave empty for default value (false).
     * This property is skipped if $configuration is set.
     * @since 2.0
     */
    public $debug;

    /**
     * @var array Whitelist of formats to allow in the editor.
     * Leave empty for default list (all allowed).
     * This property is skipped if $configuration is set.
     * @since 2.0
     */
    public $formats;

    /**
     * @var array Collection of modules to include and respective options.
     * This property is skipped if $configuration is set.
     * Notice: if you set 'toolbar' module it will replace $toolbarOptions configuration.
     * @since 2.0
     */
    public $modules;

    /**
     * @var bool Whether to instantiate the editor in read-only mode.
     * Leave empty for default value (false).
     * This property is skipped if $configuration is set.
     * @since 2.0
     */
    public $readOnly;

    /**
     * @var string Additional JS code to be called with the editor.
     * Use placeholder {quill} to get the current editor object variable's name.
     * @since 1.1
     */
    public $js;

    /**
     * @var array Quill options.
     * Set this to override all other parameters and configure Quill manually.
     * See https://quilljs.com/docs/configuration/ for details.
     * @since 2.0
     */
    public $configuration;

    /**
     * @var array HTML attributes for the input tag.
     * @see Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['style' => 'min-height:150px;'];

    /**
     * @var string HTML tag for the editor.
     * @since 2.0
     */
    public $tag = 'div';

    /**
     * @var bool
     * Use emoji smiles
     */
    public $emoji = false;

    /**
     * @inheritdoc
     */
    public static $autoIdPrefix = 'quill-';

    /**
     * @var string ID of the editor.
     */
    protected $_fieldId;

    /**
     * @var array
     * @since 2.0
     */
    protected $_quillConfiguration = [];

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {

        if (!empty($this->configuration) && !\is_array($this->configuration)) {
            throw new InvalidConfigException('The "configuration" property must be an array!');
        }

        if (!empty($this->js) && !\is_string($this->js)) {
            throw new InvalidConfigException('The "js" property must be a string!');
        }

        if (!empty($this->formats) && !\is_array($this->formats)) {
            throw new InvalidConfigException('The "formats" property must be an array!');
        }

        if (!empty($this->modules) && !\is_array($this->modules)) {
            throw new InvalidConfigException('The "modules" property must be an array!');
        }

        parent::init();

        $this->_fieldId = $this->options['id'];
        $this->options['id'] = 'editor-' . $this->id;

        $this->prepareOptions();
    }


    /**
     * Prepares Quill configuration.
     */
    protected function prepareOptions()
    {
        if (!empty($this->configuration)) {
            if (isset($this->configuration['theme'])) {
                $this->theme = $this->configuration['theme'];
            }
            $this->_quillConfiguration = $this->configuration;

        } else {
            if (!empty($this->theme)) {
                $this->_quillConfiguration['theme'] = $this->theme;
            }

            if (!empty($this->bounds)) {
                $this->_quillConfiguration['bounds'] = new JsExpression($this->bounds);
            }

            if (!empty($this->debug)) {
                $this->_quillConfiguration['debug'] = $this->debug;
            }

            if (!empty($this->placeholder)) {
                $this->_quillConfiguration['placeholder'] = $this->placeholder;
            }

            if (!empty($this->formats)) {
                $this->_quillConfiguration['formates'] = $this->formats;
            }

            if (!empty($this->modules)) {
                foreach ($this->modules as $module => $config) {
                    $this->_quillConfiguration['modules'][$module] = $config;
                }
            }

            if (!empty($this->toolbarOptions)) {
                $this->_quillConfiguration['modules']['toolbar'] = $this->renderToolbar();
            }

            if ($this->emoji) {
                $this->_quillConfiguration['modules']['emoji-toolbar'] = true;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientScript();

        if ($this->hasModel()) {
            return Html::activeHiddenInput(
                    $this->model, $this->attribute, ['id' => $this->_fieldId]
                ) . Html::tag(
                    $this->tag, $this->model->{$this->attribute}, $this->options
                );
        }

        return Html::hiddenInput(
                $this->name, $this->value, ['id' => $this->_fieldId]
            ) . Html::tag(
                $this->tag, $this->value, $this->options
            );
    }

    /**
     * Registers widget assets.
     * Note that Quill works without jQuery.
     */
    public function registerClientScript()
    {
        $view = $this->view;

        $asset = QuillAsset::register($view);
        $asset->theme = $this->theme;

        if ($this->emoji) {
            EmojiAsset::register($view);
        }

        $configs = Json::encode($this->_quillConfiguration);
        $editor = 'q_' . preg_replace('~[^0-9_\p{L}]~u', '_', $this->id);

        $js = "var $editor=new Quill(\"#editor-{$this->id}\",$configs);";
        $js .= "document.getElementById(\"editor-{$this->id}\").onclick=function(e){document.querySelector(\"#editor-{$this->id} .ql-editor\").focus();};";
        $js .= "$editor.on('text-change',function(){document.getElementById(\"{$this->_fieldId}\").value=$editor.root.innerHTML;});";

        if (!empty($this->js)) {
            $js .= str_replace('{quill}', $editor, $this->js);
        }

        $view->registerJs($js, View::POS_END);
    }

    /**
     * Prepares predefined set of buttons.
     * @return bool|array
     */
    public function renderToolbar()
    {
        if ($this->toolbarOptions === self::TOOLBAR_BASIC) {
            return [
                [
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                ],
                [
                    ['list' => 'ordered'],
                    ['list' => 'bullet'],
                ],
                [
                    ['align' => []],
                ],
                [
                    'link',
                ],
            ];
        }

        if ($this->toolbarOptions === self::TOOLBAR_FULL) {
            return [
                [
                    ['font' => []],
                    [
                        'size' => [
                            'small',
                            false,
                            'large',
                            'huge',
                        ],
                    ],
                ],
                [
                    'bold',
                    'italic',
                    'underline',
                    'strike',
                ],
                [
                    ['color' => []],
                    ['background' => []],
                ],
                [
                    ['script' => 'sub'],
                    ['script' => 'super'],
                ],
                [
                    ['header' => 1],
                    ['header' => 2],
                    'blockquote',
                    'code-block',
                ],
                [
                    ['list' => 'ordered'],
                    ['list' => 'bullet'],
                    ['indent' => '-1'],
                    ['indent' => '+1'],
                ],
                [
                    ['direction' => 'rtl'],
                    ['align' => []],
                ],
                [
                    'link',
                    'image',
                    'video',
                ],
                [
                    'clean',
                ],
            ];
        }
        if (self::recursive_array_search("emoji", $this->toolbarOptions) !== false) {
            $this->emoji = true;
        }
        return $this->toolbarOptions;
    }

    public static function recursive_array_search($needle, $haystack)
    {
        if (!is_array($haystack)){
            return false;
        }
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value OR (is_array($value) && self::recursive_array_search($needle, $value) !== false)) {
                return $current_key;
            }
        }
        return false;
    }
}
