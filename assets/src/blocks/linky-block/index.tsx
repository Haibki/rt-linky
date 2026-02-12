/**
 * RT-Linky Profile Block
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';

import metadata from './block.json';

interface Profile {
    id: number;
    title: string;
    slug: string;
}

interface BlockAttributes {
    profileId: number;
    slug: string;
}

const Edit = ({ attributes, setAttributes }: { attributes: BlockAttributes; setAttributes: (attrs: Partial<BlockAttributes>) => void }) => {
    const blockProps = useBlockProps();
    const [profiles, setProfiles] = useState<Profile[]>([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchProfiles = async () => {
            try {
                const response = await fetch((window as any).rtLinkyBlockData?.restUrl + 'profiles', {
                    headers: {
                        'X-WP-Nonce': (window as any).rtLinkyBlockData?.restNonce || '',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    setProfiles(data);
                }
            } catch (error) {
                console.error('Failed to fetch profiles:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchProfiles();
    }, []);

    const options = [
        { label: __('Select a profile', 'rt-linky'), value: 0 },
        ...profiles.map(profile => ({
            label: profile.title,
            value: profile.id,
        })),
    ];

    const selectedProfile = profiles.find(p => p.id === attributes.profileId);

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Profile Settings', 'rt-linky')}>
                    {loading ? (
                        <p>{__('Loading profiles...', 'rt-linky')}</p>
                    ) : (
                        <SelectControl
                            label={__('Select Profile', 'rt-linky')}
                            value={attributes.profileId}
                            options={options}
                            onChange={(value) => {
                                const profileId = parseInt(value as string, 10);
                                const profile = profiles.find(p => p.id === profileId);
                                setAttributes({
                                    profileId,
                                    slug: profile?.slug || '',
                                });
                            }}
                        />
                    )}
                </PanelBody>
            </InspectorControls>

            <div className="rt-linky-block-preview">
                {selectedProfile ? (
                    <div className="rt-linky-block-selected">
                        <div className="rt-linky-block-icon">🔗</div>
                        <h4>{selectedProfile.title}</h4>
                        <code>/{selectedProfile.slug}</code>
                    </div>
                ) : (
                    <div className="rt-linky-block-placeholder">
                        <div className="rt-linky-block-icon">🔗</div>
                        <p>{__('Select a profile from the sidebar', 'rt-linky')}</p>
                    </div>
                )}
            </div>
        </div>
    );
};

registerBlockType(metadata.name, {
    edit: Edit,
    save: () => null,
});
