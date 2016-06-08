<?php

namespace Unimatrix\Utility\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Inflector;

/**
 * Sluggable
 * Slugify a field from 'A nice article' to 'a-nice-article'
 *
 * Field: db field to take the slug string from (title, name)
 * Slug: db field to save the slug to (slug)
 * Replacement: replace invalid characters with this character
 * Overwrite: Overwrite the slug upon edidting the entity?
 *
 * Configuration:
 * -------------------------------------------------------
 * $this->addBehavior('Unimatrix/Utility.Sluggable', [
 *     'field' => 'title',
 *     'slug' => 'slug',
 *     'replacement' => '-',
 *     'overwrite' => false
 * ]);
 *
 * Multiple fields to sluggify at once:
 * -------------------------------------------------------
 * $this->addBehavior('Unimatrix/Utility.Sluggable', ['multiple' => [
 *     'field' => 'title_ro',
 *     'slug' => 'slug_ro',
 *     'replacement' => '-',
 *     'overwrite' => false
 * ], [
 *     'field' => 'title_en',
 *     'slug' => 'slug_en',
 *     'replacement' => '-',
 *     'overwrite' => false
 * ]]);
 *
 * Slug finder:
 * -----------------------------------
 * $this->Users->find('slug', [
 *     'field' => 'slug',
 *     'slug' => 'your-slug-here'
 * ]);
 *
 * @author Flavius
 * @version 0.2
 */
class SluggableBehavior extends Behavior {

    /**
     * Default config.
     * @var array
     */
	protected $_defaultConfig = [
		'field' => 'title',
		'slug' => 'slug',
		'replacement' => '-',
        'overwrite' => false,
	    'multiple' => []
	];

    /**
     * Slug a field passed in the default config with its replacement.
     * @param $value The string that needs to be processed
     * @param $replacement The replacement string
     * @return string
     */
	private function slug($value = null, $replacement = '-') {
        return strtolower(Inflector::slug($value, $replacement));
	}

    /**
     * BeforeSave handle.
     * @param \Cake\Event\Event  $event The beforeSave event that was fired.
     * @param \Cake\ORM\Entity $entity The entity that is going to be saved.
     * @return void
     */
	public function beforeSave(Event $event, Entity $entity) {
	    // get config
		$config = $this->config();

		// multiple fields to slugify
		if(!empty($config['multiple'])) {
		    foreach($config['multiple'] as $one)
                if(!$entity->get($one['slug']) || (isset($one['overwrite']) ? $one['overwrite'] : $config['overwrite']))
                    $entity->set($one['slug'], $this->slug($entity->get($one['field']), isset($one['replacement']) ? $one['replacement'] : $config['replacement']));

        // one field to slugify
        } else {
            if(!$entity->get($config['slug']) || $config['overwrite'])
                $entity->set($config['slug'], $this->slug($entity->get($config['field']), $config['replacement']));
        }
	}

    /**
     * Custom finder by slug.
     * @param \Cake\ORM\Query $query The query finder.
     * @param array $options The options passed in the query builder.
     * @return \Cake\ORM\Query
     */
	public function findSlug(Query $query, array $options) {
		return $query->where([
			$options['field'] => $options['slug']
		]);
	}
}