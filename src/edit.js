import { __ } from '@wordpress/i18n';
import { useBlockProps, BlockControls, InspectorControls } from '@wordpress/block-editor';
import {
    Button,
    Modal,
    RangeControl,
    ToggleControl,
    ButtonGroup,
    ToolbarGroup,
    ToolbarButton,
    Spinner,
    Placeholder,
    PanelBody,
    TextareaControl,
    TextControl,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { image as imageIcon, plus, check } from '@wordpress/icons';
import { createBlock } from '@wordpress/blocks';
import { useDispatch, useSelect } from '@wordpress/data';

export default function Edit({ attributes, setAttributes, clientId }) {
    const { images, hasGenerated } = attributes;
    const [isLoading, setIsLoading] = useState(false);
    const [orientation, setOrientation] = useState('square');
    const [quantity, setQuantity] = useState(1);
    const [autoGenerate, setAutoGenerate] = useState(true);
    const [customPrompt, setCustomPrompt] = useState('');
    const [filenameKeyword, setFilenameKeyword] = useState('');
    const [previewImage, setPreviewImage] = useState(null);

    const { replaceBlocks } = useDispatch('core/block-editor');
    const { createErrorNotice, createSuccessNotice } = useDispatch('core/notices');

    const { postTitle, postContent } = useSelect((select) => {
        const editor = select('core/editor');
        return {
            postTitle: editor.getEditedPostAttribute('title'),
            postContent: editor.getEditedPostAttribute('content'),
        };
    }, []);

    const insertImage = (img) => {
        const newBlock = createBlock('core/image', {
            id: img.id,
            url: img.url,
            alt: img.alt || '',
            caption: img.caption || '',
        });

        replaceBlocks(clientId, newBlock);
        createSuccessNotice(__('Image added to post as native block.', 'imagen-flow'), { type: 'snackbar' });
    };

    const handleGenerate = async () => {
        setIsLoading(true);
        try {
            let prompt = customPrompt;

            if (autoGenerate) {
                const essenceResult = await apiFetch({
                    path: '/imagen-flow/v1/summarize',
                    method: 'POST',
                    data: { content: (postTitle || '') + '\n' + (postContent ? postContent.substring(0, 1000) : '') },
                });

                if (essenceResult.success) {
                    prompt = essenceResult.essence;
                } else {
                    throw new Error(essenceResult.message || __('Failed to summarize content.', 'imagen-flow'));
                }
            }

            if (!prompt) {
                createErrorNotice(__('Please provide a prompt or some content.', 'imagen-flow'));
                setIsLoading(false);
                return;
            }

            const result = await apiFetch({
                path: '/imagen-flow/v1/generate',
                method: 'POST',
                data: {
                    prompt,
                    samples: quantity,
                    orientation,
                    filename_keyword: filenameKeyword
                },
            });

            if (result.success && result.images && result.images.length > 0) {
                setAttributes({
                    images: [...images, ...result.images],
                    hasGenerated: true
                });
                createSuccessNotice(__('Images generated successfully!', 'imagen-flow'), { type: 'snackbar' });
            } else {
                const errorMsg = result.message || __('API returned success but no images were generated.', 'imagen-flow');
                createErrorNotice(errorMsg);
            }
        } catch (error) {
            console.error('Generation failed', error);
            const message = error.message || __('An unknown error occurred during generation.', 'imagen-flow');
            createErrorNotice(__('Generation failed: ', 'imagen-flow') + message);
        } finally {
            setIsLoading(false);
        }
    };

    return (
        <div {...useBlockProps()}>
            <InspectorControls>
                <PanelBody title={__('Generation Settings', 'imagen-flow')}>
                    <ToggleControl
                        label={__('Auto-generate from article', 'imagen-flow')}
                        checked={autoGenerate}
                        onChange={setAutoGenerate}
                    />

                    {!autoGenerate && (
                        <TextareaControl
                            label={__('Custom Prompt', 'imagen-flow')}
                            placeholder={__('Enter custom prompt...', 'imagen-flow')}
                            value={customPrompt}
                            onChange={setCustomPrompt}
                        />
                    )}

                    <div style={{ marginBottom: '15px' }}>
                        <label className="components-base-control__label">
                            {__('Orientation', 'imagen-flow')}
                        </label>
                        <ButtonGroup style={{ width: '100%', marginTop: '5px' }}>
                            <Button
                                isPrimary={orientation === 'square'}
                                onClick={() => setOrientation('square')}
                                style={{ flex: 1 }}
                            >
                                {__('Square', 'imagen-flow')}
                            </Button>
                            <Button
                                isPrimary={orientation === 'portrait'}
                                onClick={() => setOrientation('portrait')}
                                style={{ flex: 1 }}
                            >
                                {__('Portrait', 'imagen-flow')}
                            </Button>
                            <Button
                                isPrimary={orientation === 'landscape'}
                                onClick={() => setOrientation('landscape')}
                                style={{ flex: 1 }}
                            >
                                {__('Landscape', 'imagen-flow')}
                            </Button>
                        </ButtonGroup>
                    </div>

                    <RangeControl
                        label={__('Quantity', 'imagen-flow')}
                        value={quantity}
                        onChange={setQuantity}
                        min={1}
                        max={4}
                    />

                    <TextControl
                        label={__('Naming Keyword (SEO)', 'imagen-flow')}
                        help={__('Add a brand or specific keyword for the filename.', 'imagen-flow')}
                        value={filenameKeyword}
                        onChange={setFilenameKeyword}
                    />

                    <Button
                        isPrimary
                        isBusy={isLoading}
                        onClick={handleGenerate}
                        disabled={isLoading}
                        style={{ width: '100%', justifyContent: 'center', marginTop: '10px' }}
                    >
                        {isLoading ? __('Generating...', 'imagen-flow') : __('Generate Images', 'imagen-flow')}
                    </Button>
                </PanelBody>
            </InspectorControls>

            <BlockControls>
                <ToolbarGroup>
                    <ToolbarButton
                        icon={plus}
                        label={__('Generate Image', 'imagen-flow')}
                        onClick={handleGenerate}
                        disabled={isLoading}
                    />
                </ToolbarGroup>
            </BlockControls>

            {images.length === 0 && !hasGenerated && (
                <Placeholder
                    icon={imageIcon}
                    label={__('ImagenFlow', 'imagen-flow')}
                    instructions={__('Configure generation in the sidebar and click Generate.', 'imagen-flow')}
                >
                    <Button isPrimary onClick={handleGenerate} disabled={isLoading}>
                        {isLoading ? <Spinner /> : __('Generate from Essence', 'imagen-flow')}
                    </Button>
                </Placeholder>
            )}

            <div className="imagen-flow-gallery">
                {images.map((img, index) => (
                    <div key={index} className="imagen-flow-thumbnail">
                        <img src={img.url} alt="" onClick={() => setPreviewImage(img)} />
                        <Button
                            icon={check}
                            className="insert-button"
                            isPrimary
                            onClick={(e) => {
                                e.stopPropagation();
                                insertImage(img);
                            }}
                            label={__('Insert into post', 'imagen-flow')}
                        />
                    </div>
                ))}
            </div>

            {previewImage && (
                <Modal title={__('Preview Image', 'imagen-flow')} onRequestClose={() => setPreviewImage(null)}>
                    <img src={previewImage.url} alt="" style={{ width: '100%' }} />
                    <div style={{ marginTop: '10px', display: 'flex', justifyContent: 'flex-end' }}>
                        <Button isPrimary onClick={() => setPreviewImage(null)}>
                            {__('Close', 'imagen-flow')}
                        </Button>
                    </div>
                </Modal>
            )}
        </div>
    );
}
