<?php
/**
 * Created by PhpStorm.
 * User: newexe
 * Date: 29.09.18
 * Time: 18:20
 */

interface Wpb_Archiver {
	/**
	 * @return string
	 */
	public function get_archives_dir();

	/**
	 * @param string $archives_dir
	 */
	public function set_archives_dir($archives_dir);

	/**
	 * @return string
	 */
	public function get_archive_filename();

	/**
	 * @param string $archive_filename
	 */
	public function set_archive_filename($archive_filename);

	public function get_archive_fullpath();

	public function get_errors();

	public function is_archive_created();

	public function push_file($file_path);

	public function push_files($file_paths);

	public function create_archive($remove_prefix = '');
}