<?php

namespace Unimatrix\Utility\Model\Behavior;

use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Query;
use Cake\Utility\Text;

/**
 * Sluggable
 * Slugify a field from 'A nice article' to 'a-nice-article'
 *
 * Field: db field to take the slug string from (title, name)
 * Slug: db field to save the slug to (slug)
 * Replacement: replace invalid characters with this character
 * Overwrite: overwrite the slug upon edidting the entity?
 * Unique: check if the slug already exists and make it unique
 *
 * Configuration:
 * -------------------------------------------------------
 * $this->addBehavior('Unimatrix/Utility.Sluggable', [
 *     'field' => 'title',
 *     'slug' => 'slug',
 *     'replacement' => '-',
 *     'overwrite' => false,
 *     'unique' => true
 * ]);
 *
 * Multiple fields to sluggify at once:
 * -------------------------------------------------------
 * $this->addBehavior('Unimatrix/Utility.Sluggable', ['multiple' => [
 *     'field' => 'title_ro',
 *     'slug' => 'slug_ro',
 *     'replacement' => '-',
 *     'overwrite' => false,
 *     'unique' => true
 * ], [
 *     'field' => 'title_en',
 *     'slug' => 'slug_en',
 *     'replacement' => '-',
 *     'overwrite' => false,
 *     'unique' => true
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
 * @version 0.3
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
        'unique' => true,
	    'multiple' => []
	];

    /**
     * Slug a field passed in the default config with its replacement.
     * @param $value The string that needs to be processed
     * @param $config The config array is passed here
     * @return string
     */
	private function slug($value = null, $config = []) {
	    // generate slug
	    $slug = strtolower(Text::slug($value, $config['replacement']));

        // unique slug?
        if($config['unique']) {
            // does the slug already exist in db?
    	    $field = $this->_table->alias() . '.' . $config['slug'];
    	    $conditions = [$field => $slug];
    	    $suffix = '';
    	    $i = 0;

    	    // loop till unique slug is found
            while ($this->_table->exists($conditions)) {
    			$i++;
    			$suffix	= $config['replacement'] . $i;
    			$conditions[$field] = $slug . $suffix;
    		}

    		// got suffix? append it
    		if($suffix)
    			$slug .= $suffix;
        }

		// return slug
        return $slug;
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
		    foreach($config['multiple'] as $one) {
		        // grab a config with default settings
		        $default = $config;
		        unset($default['multiple']);
		        $one = array_merge($default, $one);
		        if(!$entity->get($one['slug']) || $one['overwrite'])
		            $entity->set($one['slug'], $this->slug($entity->get($one['field']), $one));
		    }

        // one field to slugify
        } else {
            if(!$entity->get($config['slug']) || $config['overwrite'])
                $entity->set($config['slug'], $this->slug($entity->get($config['field']), $config));
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