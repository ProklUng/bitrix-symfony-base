/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

// The editor creator to use.
import ClassicEditorBase from '@ckeditor/ckeditor5-editor-classic/src/classiceditor';

import Essentials from '@ckeditor/ckeditor5-essentials/src/essentials';
import UploadAdapter from '@ckeditor/ckeditor5-adapter-ckfinder/src/uploadadapter';
import Autoformat from '@ckeditor/ckeditor5-autoformat/src/autoformat';
import Bold from '@ckeditor/ckeditor5-basic-styles/src/bold';
import Italic from '@ckeditor/ckeditor5-basic-styles/src/italic';
import BlockQuote from '@ckeditor/ckeditor5-block-quote/src/blockquote';
import CKFinder from '@ckeditor/ckeditor5-ckfinder/src/ckfinder';
import EasyImage from '@ckeditor/ckeditor5-easy-image/src/easyimage';
import Heading from '@ckeditor/ckeditor5-heading/src/heading';
import Image from '@ckeditor/ckeditor5-image/src/image';
import ImageCaption from '@ckeditor/ckeditor5-image/src/imagecaption';
import ImageStyle from '@ckeditor/ckeditor5-image/src/imagestyle';
import ImageToolbar from '@ckeditor/ckeditor5-image/src/imagetoolbar';
import ImageUpload from '@ckeditor/ckeditor5-image/src/imageupload';
import Link from '@ckeditor/ckeditor5-link/src/link';
import List from '@ckeditor/ckeditor5-list/src/list';
import MediaEmbed from '@ckeditor/ckeditor5-media-embed/src/mediaembed';
import Paragraph from '@ckeditor/ckeditor5-paragraph/src/paragraph';
import PasteFromOffice from '@ckeditor/ckeditor5-paste-from-office/src/pastefromoffice';
import Table from '@ckeditor/ckeditor5-table/src/table';
import TableToolbar from '@ckeditor/ckeditor5-table/src/tabletoolbar';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';

import Font from '@ckeditor/ckeditor5-font/src/font';
import Underline from '@ckeditor/ckeditor5-basic-styles/src/underline';
import RemoveFormat from '@ckeditor/ckeditor5-remove-format/src/removeformat';

import Plugin from '@ckeditor/ckeditor5-core/src/plugin';
import {createDropdown} from '@ckeditor/ckeditor5-ui/src/dropdown/utils';
import imageIcon from '@ckeditor/ckeditor5-core/theme/icons/image.svg';
import View from '@ckeditor/ckeditor5-ui/src/view';

import { ckeditor_handle_path } from './paths.js'; // Пути к обработчикам медиа-дел.

class MedialibItemView extends View {
    constructor(locale, data) {
        super(locale);

        const bind = this.bindTemplate;

        this.setTemplate(data);
    }
}

class MedialibView extends View {
    constructor(locale) {
        super(locale);

        const bind = this.bindTemplate;

        this.items = this.createCollection();

        this.imageUrl = '';

        this.filter = {
            collection_id: 0,
            page_num: 1
        };

        this.setTemplate({
            tag: 'div',
            attributes: {
                class: ['c-medialib']
            },
            children: this.items
        });

        this.connectToMedialib = function (params, callback) {
            function formatParams(params) {
                return "?" + Object
                    .keys(params)
                    .map(function (key) {
                        return key + "=" + encodeURIComponent(params[key])
                    })
                    .join("&")
            }

            let url = ckeditor_handle_path.medialib + formatParams(params);

            let xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                    try {
                        callback(JSON.parse(xmlhttp.responseText));
                    } catch (err) {
                    }
                }
            };

            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        };


        this.loadImages = function () {
            this.connectToMedialib(this.filter, (data) => {
                this.items.clear();

                let collectionOptions = [];
                data.collections.forEach((item) => {
                    let option = {
                        tag: 'option',
                        attributes: {
                            value: item['ID'],
                        },
                        children: [item['NAME']]
                    };
                    if (data.collection_id === parseInt(item['ID'], 10)) {
                        option.attributes.selected = "selected"
                    }

                    collectionOptions.push(option);
                });

                let collectionSelect = {
                    tag: 'select',
                    children: collectionOptions,
                    on: {
                        change: bind.to(evt => {
                            this.filter.collection_id = evt.target.value;
                            this.filter.page_num = 1;
                            this.loadImages();

                        })
                    },
                };

                let prevButton = {
                    tag: 'input',
                    attributes: {
                        type: 'button',
                        value: 'Назад',
                    },
                    on: {
                        click: bind.to(evt => {
                            this.filter.page_num--;
                            this.loadImages()
                        })
                    },
                };

                let nextButton = {
                    tag: 'input',
                    attributes: {
                        type: 'button',
                        value: 'Вперед',
                    },
                    on: {
                        click: bind.to(evt => {
                            this.filter.page_num++;
                            this.loadImages()
                        })
                    },
                };

                if (data.page_num <= 1) {
                    prevButton.attributes.disabled = "disabled"
                }

                if (data.page_num >= data.page_count) {
                    nextButton.attributes.disabled = "disabled"
                }

                this.items.add(new MedialibItemView(locale, {
                    tag: 'div',
                    attributes: {
                        class: 'c-medialib-nav'
                    },
                    children: [collectionSelect, prevButton, nextButton],
                }));

                data.items.forEach((item) => {
                    this.items.add(new MedialibItemView(locale, {
                        tag: 'span',
                        attributes: {
                            title: item['NAME'],
                            class: 'c-medialib-item'
                        },
                        on: {
                            click: bind.to(evt => {
                                this.imageUrl = item['DETAIL_SRC'];
                                this.fire('execute');
                            })
                        },
                        children: [
                            {
                                tag: 'img',
                                attributes: {
                                    src: item['SRC']
                                }
                            }
                        ],
                    }));
                });
            });
        };
    }
}

class InsertImage extends Plugin {
    init() {
        const editor = this.editor;

        editor.ui.componentFactory.add('insertImageFromMedialib', locale => {
            //const view = new ButtonView(locale);

            const command = editor.commands.get('insertImageFromMedialib');
            const view = createDropdown(locale);

            ///view.bind('isEnabled').to(command);

            view.buttonView.set({
                icon: imageIcon,
                label: 'Вставить изображение из медиабиблиотеки',
                tooltip: true
            });

            const medialibView = new MedialibView(locale);
            view.panelView.children.add(medialibView);

            medialibView.delegate('execute').to(view);

            view.buttonView.on('open', () => {
                medialibView.loadImages();
            });

            view.on('execute', () => {

                editor.model.change(writer => {
                    const imageElement = writer.createElement('image', {
                        src: medialibView.imageUrl
                    });
                    editor.model.insertContent(imageElement, editor.model.document.selection);
                });

                editor.editing.view.focus();
            });

            return view;
        });
    }
}

export default class ClassicEditor extends ClassicEditorBase {
}

// Plugins to include in the build.
ClassicEditor.builtinPlugins = [
    Essentials,
    UploadAdapter,
    Autoformat,
    Bold,
    Italic,
    BlockQuote,
    CKFinder,
    EasyImage,
    Heading,
    Image,
    ImageCaption,
    ImageStyle,
    ImageToolbar,
    ImageUpload,
    Link,
    List,
    MediaEmbed,
    Paragraph,
    PasteFromOffice,
    Table,
    TableToolbar,
    Alignment,
    InsertImage,
	Font,
	Underline,
	RemoveFormat,
];

// Editor configuration.
ClassicEditor.defaultConfig = {
    ckfinder: {
        uploadUrl: ckeditor_handle_path.connector
    },
	fontSize: {
		options: [
			'tiny',
			'default',
			'big'
		]
	},
    toolbar: {
        items: [
            'heading',
            '|',
            'alignment',
            'bold',
            'italic',
			'fontSize',
            'imageUpload',
            'insertImageFromMedialib',
            'link',
            'bulletedList',
            'numberedList',
            'blockQuote',
            'insertTable',
            'mediaEmbed',
            'undo',
            'redo'
        ]
    },
    image: {
        toolbar: [
            //'imageStyle:side',
            'imageStyle:alignLeft',
            'imageStyle:full',
            //'imageStyle:alignCenter',
            'imageStyle:alignRight',
            '|',
            'imageTextAlternative'
        ],
        styles: [
            // This option is equal to a situation where no style is applied.
            'full',

            // This represents an image aligned to the left.
            'alignLeft',

            // This represents an image aligned to the right.
            'alignRight'
        ]
    },
    table: {
        contentToolbar: [
            'tableColumn',
            'tableRow',
            'mergeTableCells'
        ]
    },
    // This value must be kept in sync with the language defined in webpack.config.js.
    language: 'ru'
};
