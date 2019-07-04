<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Frontend\Schema
 */

/**
 * Returns schema FAQ data.
 *
 * @since 11.5
 */
class WPSEO_Schema_HowTo implements WPSEO_Graph_Piece {
	/**
	 * Determine whether this graph piece is needed or not.
	 *
	 * Always false, because this graph piece adds itself using the filter API.
	 *
	 * @var bool
	 */
	private $is_needed = false;

	/**
	 * The FAQ blocks count on the current page.
	 *
	 * @var int
	 */
	private $counter;

	/**
	 * A value object with context variables.
	 *
	 * @var WPSEO_Schema_Context
	 */
	private $context;

	/**
	 * WPSEO_Schema_FAQ constructor.
	 *
	 * @param WPSEO_Schema_Context $context A value object with context variables.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct( WPSEO_Schema_Context $context ) {
		$this->counter = 0;
		$this->context = $context;

		add_filter( 'wpseo_schema_block_yoast/how-to-block', array( $this, 'render' ), 10, 2 );
	}

	/**
	 * Renders a list of questions, referencing them by ID.
	 *
	 * @return array $data Our Schema graph.
	 */
	public function generate() {
		return array();
	}

	/**
	 * Renders the How-To block into our graph.
	 *
	 * @param array $graph Our Schema data.
	 * @param array $block The How-To block content.
	 *
	 * @return mixed
	 */
	public function render( $graph, $block ) {
		$this->counter++;
		$data = array(
			'@type'            => 'HowTo',
			'@id'              => $this->context->canonical . '#howto-' . $this->counter,
			'name'             => $this->context->title,
			'mainEntityOfPage' => array( '@id' => $this->get_main_schema_id() ),
			'description'      => '',
		);

		if ( isset( $block['attrs']['jsonDescription'] ) ) {
			$data['description'] = $block['attrs']['jsonDescription'];
		}

		$this->add_duration( $data, $block['attrs'] );
		$this->add_steps( $data, $block['attrs']['steps'] );

		$graph[] = $data;

		return $graph;
	}

	/**
	 * Adds the duration of the task to the Schema.
	 *
	 * @param array $data       Our How-To schema data.
	 * @param array $attributes The block data attributes.
	 *
	 * @return array $data Our schema data.
	 */
	private function add_duration( &$data, $attributes ) {
		if ( ! empty( $attributes['hasDuration'] ) && $attributes['hasDuration'] ) {
			$days    = empty( $attributes['days'] ) ? 0 : $attributes['days'];
			$hours   = empty( $attributes['hours'] ) ? 0 : $attributes['hours'];
			$minutes = empty( $attributes['minutes'] ) ? 0 : $attributes['minutes'];

			if ( ( $days + $hours + $minutes ) > 0 ) {
				$data['totalTime'] = 'P' . $days . 'DT' . $hours . 'H' . $minutes . 'M';
			}
		}

		return $data;
	}

	/**
	 * Determines whether we're part of an article or a webpage.
	 *
	 * @return string A reference URL.
	 */
	protected function get_main_schema_id() {
		if ( $this->context->site_represents !== false && WPSEO_Schema_Article::is_article_post_type() ) {
			return $this->context->canonical . WPSEO_Schema_IDs::ARTICLE_HASH;
		}

		return $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH;
	}

	/**
	 * Determines whether or not a piece should be added to the graph.
	 *
	 * @return bool
	 */
	public function is_needed() {
		return $this->is_needed;
	}

	/**
	 * Adds the steps to our How-To output.
	 *
	 * @param array $data  Our How-To schema data.
	 * @param array $steps Our How-To block's steps.
	 */
	private function add_steps( &$data, $steps ) {
		foreach ( $steps as $step ) {
			$schema_id   = $this->context->canonical . '#' . $step['id'];
			$schema_step = array(
				'@type' => 'HowToStep',
				'url'   => $schema_id,
			);

			if ( empty( $step['jsonName'] ) ) {
				if ( empty( $step['text'] ) ) {
					continue;
				}

				$schema_step['text'] = '';

				$this->add_step_image( $schema_step, $step );

				// If there is no text and no image, don't output the step.
				if ( empty( $step['jsonText'] ) && empty( $schema_step['image'] ) ) {
					continue;
				}

				if ( ! empty( $step['jsonText'] ) ) {
					$schema_step['text'] = $step['jsonText'];
				}
			}
			else if ( empty( $step['jsonText'] ) ) {
				$schema_step['text'] = $step['jsonName'];
			}
			else {
				$schema_step['name'] = $step['jsonName'];

				$this->add_step_description( $schema_step, $step );
				$this->add_step_image( $schema_step, $step );
			}

			$data['step'][] = $schema_step;
		}
	}

	/**
	 * Checks if we have a step description, if we do, add it.
	 *
	 * @param array $schema_step Our Schema output for the Step.
	 * @param array $step        The step block data.
	 */
	private function add_step_description( &$schema_step, $step ) {
		if ( empty( $step['jsonText'] ) ) {
			return;
		}

		$schema_step['itemListElement'] = array();

		$schema_step['itemListElement'][] = array(
			'@type' => 'HowToDirection',
			'text'  => $step['jsonText'],
		);
	}

	/**
	 * Checks if we have a step image, if we do, add it.
	 *
	 * @param array $schema_step Our Schema output for the Step.
	 * @param array $step        The step block data.
	 */
	private function add_step_image( &$schema_step, $step ) {
		foreach ( $step['text'] as $line ) {
			if ( is_array( $line ) && isset( $line['type'] ) && $line['type'] === 'img' ) {
				$schema_step['image'] = $this->get_image_schema( $line['props']['src'] );
			}
		}
	}

	/**
	 * Generates the image schema from the attachment $url.
	 *
	 * @param string $url Attachment url.
	 *
	 * @return array Image schema.
	 *
	 * @codeCoverageIgnore
	 */
	protected function get_image_schema( $url ) {
		$id = WPSEO_Image_Utils::get_attachment_by_url( $url );

		$image = new WPSEO_Schema_Image( $this->context->canonical . '#schema-image-' . $id );

		return $image->generate_from_attachment_id( $id );
	}
}