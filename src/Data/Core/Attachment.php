<?php

namespace WordPress\Data\Core;

class Attachment extends Post
{
	public $post_status = 'inherit';
	public $ping_status = 'closed';
	public $post_type = 'attachment';
}
