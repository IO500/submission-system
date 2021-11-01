<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * SubmissionsFixture
 */
class SubmissionsFixture extends TestFixture
{
    /**
     * Fields
     *
     * @var array
     */
    // phpcs:disable
    public $fields = [
        'id' => ['type' => 'biginteger', 'length' => null, 'unsigned' => true, 'null' => false, 'default' => null, 'comment' => '', 'autoIncrement' => true, 'precision' => null],
        'release_id' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'information_system' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_institution' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_storage_vendor' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_filesystem_type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_client_nodes' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'information_client_total_procs' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'io500_score' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'io500_bw' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'io500_md' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'io500_tot_iops' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'information_data' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_10_node_challenge' => ['type' => 'boolean', 'length' => null, 'null' => false, 'default' => '0', 'comment' => '', 'precision' => null],
        'information_list_name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_identifier' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_submitter' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_submission_date' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'information_embargo_end_date' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_storage_install_date' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_storage_refresh_date' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_filesystem_name' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_filesystem_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_client_procs_per_node' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_client_operating_system' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_client_operating_system_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_client_kernel_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_md_nodes' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'information_md_storage_devices' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'information_md_storage_type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_md_volatile_memory_capacity' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_md_storage_interface' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_md_network' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_md_software_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_md_operating_system_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_ds_nodes' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'information_ds_storage_devices' => ['type' => 'integer', 'length' => null, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null, 'autoIncrement' => null],
        'information_ds_storage_type' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_ds_volatile_memory_capacity' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_ds_storage_interface' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_ds_network' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_ds_software_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_ds_operating_system_version' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_note' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'information_best' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'ior_easy_write' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'ior_easy_read' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'ior_hard_write' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'ior_hard_read' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_easy_write' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_easy_stat' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_easy_delete' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_hard_write' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_hard_read' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_hard_stat' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'mdtest_hard_delete' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'find_easy' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'find_hard' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => true, 'default' => null, 'comment' => ''],
        'find_mixed' => ['type' => 'decimal', 'length' => 15, 'precision' => 6, 'unsigned' => false, 'null' => false, 'default' => null, 'comment' => ''],
        'marker_score' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'marker_md' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'storage_data' => ['type' => 'text', 'length' => null, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'status' => ['type' => 'text', 'length' => null, 'null' => false, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        'include_in_io500' => ['type' => 'boolean', 'length' => null, 'null' => true, 'default' => '1', 'comment' => '', 'precision' => null],
        'valid_from' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'valid_to' => ['type' => 'date', 'length' => null, 'null' => true, 'default' => null, 'comment' => '', 'precision' => null],
        'cdcl_url' => ['type' => 'string', 'length' => 255, 'null' => true, 'default' => null, 'collate' => 'utf8_bin', 'comment' => '', 'precision' => null],
        '_constraints' => [
            'primary' => ['type' => 'primary', 'columns' => ['id'], 'length' => []],
            'id' => ['type' => 'unique', 'columns' => ['id'], 'length' => []],
        ],
        '_options' => [
            'engine' => 'InnoDB',
            'collation' => 'utf8_bin'
        ],
    ];
    // phpcs:enable
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'release_id' => 1,
                'information_system' => 'Lorem ipsum dolor sit amet',
                'information_institution' => 'Lorem ipsum dolor sit amet',
                'information_storage_vendor' => 'Lorem ipsum dolor sit amet',
                'information_filesystem_type' => 'Lorem ipsum dolor sit amet',
                'information_client_nodes' => 1,
                'information_client_total_procs' => 1,
                'io500_score' => 1.5,
                'io500_bw' => 1.5,
                'io500_md' => 1.5,
                'io500_tot_iops' => 1.5,
                'information_data' => 'Lorem ipsum dolor sit amet',
                'information_10_node_challenge' => 1,
                'information_list_name' => 'Lorem ipsum dolor sit amet',
                'information_identifier' => 'Lorem ipsum dolor sit amet',
                'information_submitter' => 'Lorem ipsum dolor sit amet',
                'information_submission_date' => '2021-08-28',
                'information_embargo_end_date' => 'Lorem ipsum dolor sit amet',
                'information_storage_install_date' => 'Lorem ipsum dolor sit amet',
                'information_storage_refresh_date' => 'Lorem ipsum dolor sit amet',
                'information_filesystem_name' => 'Lorem ipsum dolor sit amet',
                'information_filesystem_version' => 'Lorem ipsum dolor sit amet',
                'information_client_procs_per_node' => 'Lorem ipsum dolor sit amet',
                'information_client_operating_system' => 'Lorem ipsum dolor sit amet',
                'information_client_operating_system_version' => 'Lorem ipsum dolor sit amet',
                'information_client_kernel_version' => 'Lorem ipsum dolor sit amet',
                'information_md_nodes' => 1,
                'information_md_storage_devices' => 1,
                'information_md_storage_type' => 'Lorem ipsum dolor sit amet',
                'information_md_volatile_memory_capacity' => 'Lorem ipsum dolor sit amet',
                'information_md_storage_interface' => 'Lorem ipsum dolor sit amet',
                'information_md_network' => 'Lorem ipsum dolor sit amet',
                'information_md_software_version' => 'Lorem ipsum dolor sit amet',
                'information_md_operating_system_version' => 'Lorem ipsum dolor sit amet',
                'information_ds_nodes' => 1,
                'information_ds_storage_devices' => 1,
                'information_ds_storage_type' => 'Lorem ipsum dolor sit amet',
                'information_ds_volatile_memory_capacity' => 'Lorem ipsum dolor sit amet',
                'information_ds_storage_interface' => 'Lorem ipsum dolor sit amet',
                'information_ds_network' => 'Lorem ipsum dolor sit amet',
                'information_ds_software_version' => 'Lorem ipsum dolor sit amet',
                'information_ds_operating_system_version' => 'Lorem ipsum dolor sit amet',
                'information_note' => 'Lorem ipsum dolor sit amet',
                'information_best' => 'Lorem ipsum dolor sit amet',
                'ior_easy_write' => 1.5,
                'ior_easy_read' => 1.5,
                'ior_hard_write' => 1.5,
                'ior_hard_read' => 1.5,
                'mdtest_easy_write' => 1.5,
                'mdtest_easy_stat' => 1.5,
                'mdtest_easy_delete' => 1.5,
                'mdtest_hard_write' => 1.5,
                'mdtest_hard_read' => 1.5,
                'mdtest_hard_stat' => 1.5,
                'mdtest_hard_delete' => 1.5,
                'find_easy' => 1.5,
                'find_hard' => 1.5,
                'find_mixed' => 1.5,
                'marker_score' => 'Lorem ipsum dolor sit amet',
                'marker_md' => 'Lorem ipsum dolor sit amet',
                'storage_data' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'status' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
                'include_in_io500' => 1,
                'valid_from' => '2021-08-28',
                'valid_to' => '2021-08-28',
                'cdcl_url' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}
