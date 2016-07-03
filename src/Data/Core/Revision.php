<?php

namespace WordPress\Data\Core;

class Revision extends Post
{
	public $post_status = 'inherit';
	public $ping_status = 'closed';
	public $post_type = 'revision';
}
