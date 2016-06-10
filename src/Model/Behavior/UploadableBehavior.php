<?php

namespace Unimatrix\Utility\Model\Behavior;

use ArrayObject;
use Cake\Database\Type;
use Cake\Event\Event;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Unimatrix\Utility\Validation;

/**
 * Uploadable
 * Easy file upload for each defined field
 *
 * Configuration:
 * -------------------------------------------------------
 * 1. DB field should be 'avatar'.
 * 2. Add this behavior in the model table:
 *    $this->addBehavior('Unimatrix/Utility.Uploadable', [
 *        'suffix' => '_upload',
 *        'fields' => [
 *            'avatar' => 'upload/:model/:md5'
 *        ]
 *    ]);
 * 3. Load upload form widget:
 *    $this->loadHelper('Form', ['widgets' => [
 *        'upload' => ['Unimatrix/Utility.Upload']
 *    ]]);
 * 4. Use in a form
 *    echo $this->Form->input('avatar', ['type' => 'upload', 'suffix' => '_upload']);
 * 5. Optional, you can also add validation in the model table
 *    $validator
 *        ->requirePresence('avatar_upload', 'create')
 *        ->allowEmpty('avatar_upload', 'update');
 * -------------------------------------------------------
 * The suffix configuration is optional, just make sure it's the same
 * everywhere: behavior, form and validation
 *
 * Identifiers:
 * -------------------------------------------------------
 * :model: The model name
 * :md5: A random and unique identifier with 32 characters.
 *
 * eg: upload/:model/:md5 -> upload/users/5e3e0d0f163196cb9526d97be1b2ce26.jpg
 *
 * @author Flavius
 * @version 0.1
 */
class UploadableBehavior extends Behavior
{
    /**
     * Default config.
     * @var array
     */
    protected $_defaultConfig = [
        'suffix' => '_upload',
        'root' => WWW_ROOT,
        'fields' => []
    ];

    /**
     * Build the behaviour
     * @param array $config Passed configuration
     * @return void
     */
    public function initialize(array $config) {
        // get config
        $config = $this->_config;

        // load the file type & schema
        Type::map('unimatrix.file', '\Unimatrix\Utility\Database\Type\FileType');
        $schema = $this->_table->schema();

        // go through each field and change the column type to our file type
        foreach($config['fields'] as $field => $path)
            $schema->columnType($field . $config['suffix'], 'unimatrix.file');

        // update schema
        $this->_table->schema($schema);
    }

    /**
     * If a field is allowed to be empty as defined in the validation it should be unset to prevent processing
     * @param \Cake\Event\Event $event Event instance
     * @param ArrayObject $data Data to process
     * @return void
     */
    public function beforeMarshal(Event $event, ArrayObject $data) {
        // get config
        $config = $this->_config;

        // load validator and add our custom upload validator
        $validator = $this->_table->validator();
        $validator->provider('upload', Validation\UploadValidation::class);

        // go through each field
        foreach($config['fields'] as $field => $path) {
            // set virtual upload field
            $virtual = $field . $config['suffix'];

            // add validators
            $validator->add($virtual, [
                'isUnderPhpSizeLimit' => ['rule' => 'isUnderPhpSizeLimit', 'provider' => 'upload', 'message' => 'This file is too large'],
                'isUnderFormSizeLimit' => ['rule' => 'isUnderFormSizeLimit', 'provider' => 'upload', 'message' => 'This file is too large'],
                'isCompletedUpload' => ['rule' => 'isCompletedUpload', 'provider' => 'upload', 'message' => 'This file was only partially uploaded'],
                'isTemporaryDirectory' => ['rule' => 'isTemporaryDirectory', 'provider' => 'upload', 'message' => 'Missing a temporary folder'],
                'isSuccessfulWrite' => ['rule' => 'isSuccessfulWrite', 'provider' => 'upload', 'message' => 'Failed to write file to disk'],
                'isNotStoppedByExtension' => ['rule' => 'isNotStoppedByExtension', 'provider' => 'upload', 'message' => 'Upload was stopped by extension'],
            ]);

            // empty allowed? && no file uploaded? unset field
            if($validator->isEmptyAllowed($field, false)
                && isset($data[$virtual]['error']) && $data[$virtual]['error'] === UPLOAD_ERR_NO_FILE)
                    unset($data[$virtual]);
        }
    }

    /**
     * beforeSave handle
     * @param \Cake\Event\Event  $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
    public function beforeSave(Event $event, Entity $entity) {
        // get config
        $config = $this->_config;

        // go through each field
        foreach ($config['fields'] as $field => $path) {
            // set virtual upload field
            $virtual = $field . $config['suffix'];

            // no field or field not array? skip
            if(!isset($entity[$virtual]) || !is_array($entity[$virtual]))
                continue;

            // get uploaded file information
            $file = $entity->get($virtual);

            // file not ok? skip
            if((int)$file['error'] !== UPLOAD_ERR_OK)
                continue;

            // get the final name
            $final = $this->path($entity, $file, $path);

            // create the folder
            $folder = new Folder($config['root']);
            $folder->create($config['root'] . dirname($final));

            // upload the file
            $file = new File($file['tmp_name'], false);
            if($file->copy($config['root'] . $final)) {
                // get previous file and delete it
                $previous = $entity->get($field);
                if($previous) {
                    $old = new File($config['root'] . str_replace('/', DS, $previous), false);
                    if($old->exists())
                        $old->delete();
                }

                // set new file
                $entity->set($field, str_replace(DS, '/', $final));
            }

            // unset virtual
            $entity->unsetProperty($virtual);
        }
    }

    /**
     * afterDelete handle
     * @param \Cake\Event\Event  $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
    public function afterDelete(Event $event, Entity $entity) {
        // get config
        $config = $this->_config;

        // go through each field
        foreach ($config['fields'] as $field => $path) {
            // get uploaded file and delete it
            $previous = $entity->get($field);
            if($previous) {
                $old = new File($config['root'] . str_replace('/', DS, $previous), false);
                if($old->exists())
                    $old->delete();
            }
        }
    }

    /**
     * Get the path formatted without its identifiers to upload the file.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @param array $file The file array
     * @param string $path The path
     * @return string
     */
    private function path(Entity $entity, $file = [], $path = false) {
        // get extension & path
        $extension = (new File($file['name'], false))->ext();
        $path = trim(str_replace(['/', '\\'], DS, $path), DS);

        // handle identifiers
        $identifiers = [
            ':model' => strtolower($entity->source()),
            ':md5' => md5(rand() . uniqid() . time())
        ];

        // output
        return strtr($path, $identifiers) . '.' . strtolower($extension);
    }
}