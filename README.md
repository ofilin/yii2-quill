# yii2-quill

This is fork of https://github.com/bizley/yii2-quill with local library of Quill and without `Katex`, `Highlight` plugins.

*Yii 2 implementation of Quill, modern WYSIWYG editor.*

## Quill

You can find Quill at https://quilljs.com/  
- [Documentation](https://quilljs.com/docs/quickstart/)
- [Guides](https://quilljs.com/guides/why-quill/)
- [Playground](https://quilljs.com/playground/)
- [GitHub](https://github.com/quilljs/quill)

## yii2-quill

* Added Emoji support

### Installation

Add the package to your `composer.json`:

    {
        "require": {
            "ofilin/quill": "^0.3"
        }
    }

and run `composer update` or alternatively run `composer require ofilin/quill:^0.1`

### Usage

Use it as an active field extension  

    <?= $form->field($model, $attribute)->widget(\ofilin\quill\Quill::class, []) ?>

Or as a standalone widget
  
    <?= \ofilin\quill\Quill::widget(['name' => 'editor', 'value' => '']) ?>

With Emoji plugin
```
<?= $form->field($model, 'text_msg')->label(false)->widget(\ofilin\quill\Quill::class, [
    'theme' => 'snow',
    'placeholder' => 'Text',
    'toolbarOptions' => [
        ["bold", "italic", "code", "link", "emoji"],
    ],
]) ?>
```
