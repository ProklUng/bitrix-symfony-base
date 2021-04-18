document.addEventListener('DOMContentLoaded',function () {
    function initCkEditor(selector) {
        ClassicEditor
            .create(selector, {
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'alignment',
                        'bold',
                        'italic',
                        'Underline',
                        'fontFamily',
                        'fontSize',
                        'fontColor',
                        'imageUpload',
                        'insertImageFromMedialib',
                        'link',
                        'bulletedList',
                        'numberedList',
                        'blockQuote',
                        'insertTable',
                        'mediaEmbed',
                        'undo',
                        'redo',
                        'RemoveFormat'
                    ]
                },

            })
            .then(editor => {
                //window.editor = editor;
            })
            .catch(err => {
                //console.error(err.stack);
            });
    }


    function initEditor(fieldName) {

        // Подразделы.
        const sectionsTextArea = document.querySelector('textarea[name="' + fieldName + '"]');
        if (sectionsTextArea) {
            //удаляем колонку "Тип описания"
            const typeDescriptionSection = document.querySelector('input[name="' + fieldName + '_TYPE"]');

            if (typeDescriptionSection) {
                typeDescriptionSection.parentElement.parentElement.style.display = 'none';
                typeDescriptionSection.parentElement.style.display = 'none';
            }

            initCkEditor(sectionsTextArea);
            return;
        }

        const container = document.querySelector('#tr_' + fieldName + ' td');

        if (!container) {
            return false;
        }

        const textarea = container.querySelector('textarea');
        if (!textarea) {
            return false;
        }

        //убираем выравнивание у колонки, конфликтует со стилями редактора
        container.removeAttribute('align');

        //удаляем колонку "Тип описания"
        const typeDescription = document.querySelector('#tr_' + fieldName + '_TYPE');
        if (typeDescription) {
            typeDescription.remove();
        }

        const input = document.createElement('input', {
                'name': fieldName + '_TYPE',
                'type': 'hidden',
                'value': 'html'
            });

        //добавляем явное указание типа описания html
        container.appendChild(input);

        initCkEditor(textarea);

        return true;
    }

    if (typeof (ClassicEditor) != 'undefined') {
        initEditor('PREVIEW_TEXT');
        initEditor('DETAIL_TEXT');
        initEditor('DESCRIPTION');
    }

});
