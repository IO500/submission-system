<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Submissions Model
 *
 * @property \App\Model\Table\ReleasesTable&\Cake\ORM\Association\BelongsTo $Releases
 * @property \CakeDC\Users\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \App\Model\Table\QuestionnairesTable&\Cake\ORM\Association\HasMany $Questionnaires
 * @property \App\Model\Table\ListingsTable&\Cake\ORM\Association\BelongsToMany $Listings
 *
 * @method \App\Model\Entity\Submission newEmptyEntity()
 * @method \App\Model\Entity\Submission newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Submission[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Submission get($primaryKey, $options = [])
 * @method \App\Model\Entity\Submission findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Submission patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Submission[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Submission|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Submission saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Submission[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Submission[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Submission[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Submission[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class SubmissionsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('submissions');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->belongsTo('Releases', [
            'foreignKey' => 'release_id',
        ]);

        $this->belongsTo('Users', [
            'className' => 'CakeDC/Users.Users',
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ]);

        $this->belongsTo('Status', [
            'foreignKey' => 'status_id',
        ]);

        $this->belongsToMany('Listings', [
            'foreignKey' => 'submission_id',
            'targetForeignKey' => 'listing_id',
            'joinTable' => 'listings_submissions',
        ]);

        $this->hasOne('Questionnaires', [
            'foreignKey' => 'submission_id',
        ]);

        $this->addBehavior('Josegonzalez/Upload.Upload', [
            'result_tar' => [
                'path' => 'webroot{DS}files{DS}submissions{DS}{field-value:upload_hash}{DS}',
                'fields' => [
                    'dir' => 'result_tar_dir',
                    'size' => 'result_tar_size',
                    'type' => 'result_tar_type',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return strtolower($data->getClientFilename());
                },
                'keepFilesOnDelete' => false,
            ],
            'job_script' => [
                'path' => 'webroot{DS}files{DS}submissions{DS}{field-value:upload_hash}{DS}',
                'fields' => [
                    'dir' => 'job_script_dir',
                    'size' => 'job_script_size',
                    'type' => 'job_script_type',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return strtolower($data->getClientFilename());
                },
                'keepFilesOnDelete' => false,
            ],
            'job_output' => [
                'path' => 'webroot{DS}files{DS}submissions{DS}{field-value:upload_hash}{DS}',
                'fields' => [
                    'dir' => 'job_output_dir',
                    'size' => 'job_output_size',
                    'type' => 'job_output_type',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return strtolower($data->getClientFilename());
                },
                'keepFilesOnDelete' => false,
            ],
            'system_information' => [
                'path' => 'webroot{DS}files{DS}submissions{DS}{field-value:upload_hash}{DS}',
                'fields' => [
                    'dir' => 'system_information_dir',
                    'size' => 'system_information_size',
                    'type' => 'system_information_type',
                ],
                'nameCallback' => function ($table, $entity, $data, $field, $settings) {
                    return strtolower($data->getClientFilename());
                },
                'keepFilesOnDelete' => false,
            ],
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator->setProvider('upload', \Josegonzalez\Upload\Validation\DefaultValidation::class);

        $validator->add('result_tar', 'fileSuccessfulWrite', [
            'rule' => 'isSuccessfulWrite',
            'message' => 'This upload failed',
            'provider' => 'upload',
        ]);

        $validator
            ->allowEmptyString('id', null, 'create')
            ->add('id', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('information_system')
            ->maxLength('information_system', 255)
            ->allowEmptyString('information_system');

        $validator
            ->scalar('information_institution')
            ->maxLength('information_institution', 255)
            ->allowEmptyString('information_institution');

        $validator
            ->scalar('information_storage_vendor')
            ->maxLength('information_storage_vendor', 255)
            ->allowEmptyString('information_storage_vendor');

        $validator
            ->scalar('information_filesystem_type')
            ->maxLength('information_filesystem_type', 255)
            ->allowEmptyFile('information_filesystem_type');

        $validator
            ->integer('information_client_nodes')
            ->allowEmptyString('information_client_nodes');

        $validator
            ->integer('information_client_total_procs')
            ->allowEmptyString('information_client_total_procs');

        $validator
            ->decimal('io500_score')
            ->allowEmptyString('io500_score');

        $validator
            ->decimal('io500_bw')
            ->allowEmptyString('io500_bw');

        $validator
            ->decimal('io500_md')
            ->allowEmptyString('io500_md');

        $validator
            ->decimal('io500_tot_iops')
            ->allowEmptyString('io500_tot_iops');

        $validator
            ->scalar('information_data')
            ->maxLength('information_data', 255)
            ->allowEmptyString('information_data');

        $validator
            ->boolean('information_10_node_challenge')
            ->notEmptyString('information_10_node_challenge');

        $validator
            ->scalar('information_list_name')
            ->maxLength('information_list_name', 255)
            ->allowEmptyString('information_list_name');

        $validator
            ->scalar('information_identifier')
            ->maxLength('information_identifier', 255)
            ->allowEmptyString('information_identifier');

        $validator
            ->scalar('information_submitter')
            ->maxLength('information_submitter', 255)
            ->allowEmptyString('information_submitter');

        $validator
            ->date('information_submission_date')
            ->allowEmptyDate('information_submission_date');

        $validator
            ->scalar('information_embargo_end_date')
            ->maxLength('information_embargo_end_date', 255)
            ->allowEmptyString('information_embargo_end_date');

        $validator
            ->scalar('information_storage_install_date')
            ->maxLength('information_storage_install_date', 255)
            ->allowEmptyString('information_storage_install_date');

        $validator
            ->scalar('information_storage_refresh_date')
            ->maxLength('information_storage_refresh_date', 255)
            ->allowEmptyString('information_storage_refresh_date');

        $validator
            ->scalar('information_filesystem_name')
            ->maxLength('information_filesystem_name', 255)
            ->allowEmptyFile('information_filesystem_name');

        $validator
            ->scalar('information_filesystem_version')
            ->maxLength('information_filesystem_version', 255)
            ->allowEmptyFile('information_filesystem_version');

        $validator
            ->scalar('information_client_procs_per_node')
            ->maxLength('information_client_procs_per_node', 255)
            ->allowEmptyString('information_client_procs_per_node');

        $validator
            ->scalar('information_client_operating_system')
            ->maxLength('information_client_operating_system', 255)
            ->allowEmptyString('information_client_operating_system');

        $validator
            ->scalar('information_client_operating_system_version')
            ->maxLength('information_client_operating_system_version', 255)
            ->allowEmptyString('information_client_operating_system_version');

        $validator
            ->scalar('information_client_kernel_version')
            ->maxLength('information_client_kernel_version', 255)
            ->allowEmptyString('information_client_kernel_version');

        $validator
            ->integer('information_md_nodes')
            ->allowEmptyString('information_md_nodes');

        $validator
            ->integer('information_md_storage_devices')
            ->allowEmptyString('information_md_storage_devices');

        $validator
            ->scalar('information_md_storage_type')
            ->maxLength('information_md_storage_type', 255)
            ->allowEmptyString('information_md_storage_type');

        $validator
            ->scalar('information_md_volatile_memory_capacity')
            ->maxLength('information_md_volatile_memory_capacity', 255)
            ->allowEmptyString('information_md_volatile_memory_capacity');

        $validator
            ->scalar('information_md_storage_interface')
            ->maxLength('information_md_storage_interface', 255)
            ->allowEmptyString('information_md_storage_interface');

        $validator
            ->scalar('information_md_network')
            ->maxLength('information_md_network', 255)
            ->allowEmptyString('information_md_network');

        $validator
            ->scalar('information_md_software_version')
            ->maxLength('information_md_software_version', 255)
            ->allowEmptyString('information_md_software_version');

        $validator
            ->scalar('information_md_operating_system_version')
            ->maxLength('information_md_operating_system_version', 255)
            ->allowEmptyString('information_md_operating_system_version');

        $validator
            ->integer('information_ds_nodes')
            ->allowEmptyString('information_ds_nodes');

        $validator
            ->integer('information_ds_storage_devices')
            ->allowEmptyString('information_ds_storage_devices');

        $validator
            ->scalar('information_ds_storage_type')
            ->maxLength('information_ds_storage_type', 255)
            ->allowEmptyString('information_ds_storage_type');

        $validator
            ->scalar('information_ds_volatile_memory_capacity')
            ->maxLength('information_ds_volatile_memory_capacity', 255)
            ->allowEmptyString('information_ds_volatile_memory_capacity');

        $validator
            ->scalar('information_ds_storage_interface')
            ->maxLength('information_ds_storage_interface', 255)
            ->allowEmptyString('information_ds_storage_interface');

        $validator
            ->scalar('information_ds_network')
            ->maxLength('information_ds_network', 255)
            ->allowEmptyString('information_ds_network');

        $validator
            ->scalar('information_ds_software_version')
            ->maxLength('information_ds_software_version', 255)
            ->allowEmptyString('information_ds_software_version');

        $validator
            ->scalar('information_ds_operating_system_version')
            ->maxLength('information_ds_operating_system_version', 255)
            ->allowEmptyString('information_ds_operating_system_version');

        $validator
            ->scalar('information_note')
            ->maxLength('information_note', 255)
            ->allowEmptyString('information_note');

        $validator
            ->scalar('information_best')
            ->maxLength('information_best', 255)
            ->allowEmptyString('information_best');

        $validator
            ->decimal('ior_easy_write')
            ->allowEmptyString('ior_easy_write');

        $validator
            ->decimal('ior_easy_read')
            ->allowEmptyString('ior_easy_read');

        $validator
            ->decimal('ior_hard_write')
            ->allowEmptyString('ior_hard_write');

        $validator
            ->decimal('ior_hard_read')
            ->allowEmptyString('ior_hard_read');

        $validator
            ->decimal('mdtest_easy_write')
            ->allowEmptyString('mdtest_easy_write');

        $validator
            ->decimal('mdtest_easy_stat')
            ->allowEmptyString('mdtest_easy_stat');

        $validator
            ->decimal('mdtest_easy_delete')
            ->allowEmptyString('mdtest_easy_delete');

        $validator
            ->decimal('mdtest_hard_write')
            ->allowEmptyString('mdtest_hard_write');

        $validator
            ->decimal('mdtest_hard_read')
            ->allowEmptyString('mdtest_hard_read');

        $validator
            ->decimal('mdtest_hard_stat')
            ->allowEmptyString('mdtest_hard_stat');

        $validator
            ->decimal('mdtest_hard_delete')
            ->allowEmptyString('mdtest_hard_delete');

        $validator
            ->decimal('find_easy')
            ->allowEmptyString('find_easy');

        $validator
            ->decimal('find_hard')
            ->allowEmptyString('find_hard');

        $validator
            ->decimal('find_mixed')
            ->allowEmptyString('find_mixed');

        $validator
            ->scalar('marker_score')
            ->maxLength('marker_score', 255)
            ->allowEmptyString('marker_score');

        $validator
            ->scalar('marker_md')
            ->maxLength('marker_md', 255)
            ->allowEmptyString('marker_md');

        $validator
            ->scalar('storage_data')
            ->allowEmptyString('storage_data');

        $validator
            ->boolean('include_in_io500')
            ->allowEmptyString('include_in_io500');

        $validator
            ->date('valid_from')
            ->allowEmptyDate('valid_from');

        $validator
            ->date('valid_to')
            ->allowEmptyDate('valid_to');

        $validator
            ->scalar('cdcl_url')
            ->maxLength('cdcl_url', 255)
            ->allowEmptyString('cdcl_url');

        $validator
            ->integer('information_client_sockets')
            ->requirePresence('information_client_sockets', 'create')
            ->notEmptyString('information_client_sockets');

        $validator
            ->scalar('information_ds_clock')
            ->maxLength('information_ds_clock', 25)
            ->allowEmptyString('information_ds_clock');

        $validator
            ->integer('information_ds_media_secondary_count')
            ->allowEmptyString('information_ds_media_secondary_count');

        $validator
            ->scalar('information_ds_media_primary_vendor')
            ->maxLength('information_ds_media_primary_vendor', 255)
            ->allowEmptyString('information_ds_media_primary_vendor');

        $validator
            ->scalar('information_ds_model')
            ->maxLength('information_ds_model', 255)
            ->allowEmptyString('information_ds_model');

        $validator
            ->scalar('information_ds_media_secondary_capacity')
            ->maxLength('information_ds_media_secondary_capacity', 25)
            ->allowEmptyString('information_ds_media_secondary_capacity');

        $validator
            ->boolean('information_md_spdk')
            ->allowEmptyString('information_md_spdk');

        $validator
            ->scalar('information_md_media_secondary_vendor')
            ->maxLength('information_md_media_secondary_vendor', 255)
            ->allowEmptyString('information_md_media_secondary_vendor');

        $validator
            ->scalar('information_ds_kernel_version')
            ->maxLength('information_ds_kernel_version', 255)
            ->allowEmptyString('information_ds_kernel_version');

        $validator
            ->scalar('information_client_interconnect_vendor')
            ->maxLength('information_client_interconnect_vendor', 255)
            ->requirePresence('information_client_interconnect_vendor', 'create')
            ->notEmptyString('information_client_interconnect_vendor');

        $validator
            ->boolean('information_ds_interconnect_rdma')
            ->allowEmptyString('information_ds_interconnect_rdma');

        $validator
            ->scalar('information_ds_media_secondary_vendor')
            ->maxLength('information_ds_media_secondary_vendor', 255)
            ->allowEmptyString('information_ds_media_secondary_vendor');

        $validator
            ->boolean('information_client_dpdk')
            ->requirePresence('information_client_dpdk', 'create')
            ->notEmptyString('information_client_dpdk');

        $validator
            ->integer('information_md_interconnect_links')
            ->allowEmptyString('information_md_interconnect_links');

        $validator
            ->boolean('information_md_dpdk')
            ->allowEmptyString('information_md_dpdk');

        $validator
            ->scalar('information_ds_architecture')
            ->maxLength('information_ds_architecture', 255)
            ->allowEmptyString('information_ds_architecture');

        $validator
            ->scalar('information_md_interconnect_vendor')
            ->maxLength('information_md_interconnect_vendor', 255)
            ->allowEmptyString('information_md_interconnect_vendor');

        $validator
            ->integer('information_ds_interconnect_links')
            ->allowEmptyString('information_ds_interconnect_links');

        $validator
            ->scalar('information_client_interconnect_type')
            ->maxLength('information_client_interconnect_type', 255)
            ->requirePresence('information_client_interconnect_type', 'create')
            ->notEmptyString('information_client_interconnect_type');

        $validator
            ->integer('information_ds_cores_per_socket')
            ->allowEmptyString('information_ds_cores_per_socket');

        $validator
            ->integer('information_md_media_primary_count')
            ->allowEmptyString('information_md_media_primary_count');

        $validator
            ->integer('information_md_media_secondary_count')
            ->allowEmptyString('information_md_media_secondary_count');

        $validator
            ->boolean('information_client_spdk')
            ->requirePresence('information_client_spdk', 'create')
            ->notEmptyString('information_client_spdk');

        $validator
            ->scalar('information_md_media_secondary_capacity')
            ->maxLength('information_md_media_secondary_capacity', 255)
            ->allowEmptyString('information_md_media_secondary_capacity');

        $validator
            ->scalar('information_md_kernel_version')
            ->maxLength('information_md_kernel_version', 255)
            ->allowEmptyString('information_md_kernel_version');

        $validator
            ->scalar('information_ds_interconnect_type')
            ->maxLength('information_ds_interconnect_type', 255)
            ->allowEmptyString('information_ds_interconnect_type');

        $validator
            ->scalar('information_ds_interconnect_vendor')
            ->maxLength('information_ds_interconnect_vendor', 255)
            ->allowEmptyString('information_ds_interconnect_vendor');

        $validator
            ->scalar('information_md_architecture')
            ->maxLength('information_md_architecture', 255)
            ->allowEmptyString('information_md_architecture');

        $validator
            ->integer('information_ds_sockets')
            ->allowEmptyString('information_ds_sockets');

        $validator
            ->boolean('information_md_interconnect_rdma')
            ->allowEmptyString('information_md_interconnect_rdma');

        $validator
            ->scalar('information_client_model')
            ->maxLength('information_client_model', 255)
            ->requirePresence('information_client_model', 'create')
            ->notEmptyString('information_client_model');

        $validator
            ->scalar('information_md_media_secondary_interface')
            ->maxLength('information_md_media_secondary_interface', 255)
            ->allowEmptyString('information_md_media_secondary_interface');

        $validator
            ->integer('information_client_interconnect_links')
            ->requirePresence('information_client_interconnect_links', 'create')
            ->notEmptyString('information_client_interconnect_links');

        $validator
            ->scalar('information_ds_operating_system')
            ->maxLength('information_ds_operating_system', 255)
            ->allowEmptyString('information_ds_operating_system');

        $validator
            ->boolean('information_ds_spdk')
            ->allowEmptyString('information_ds_spdk');

        $validator
            ->integer('information_client_cores_per_socket')
            ->requirePresence('information_client_cores_per_socket', 'create')
            ->notEmptyString('information_client_cores_per_socket');

        $validator
            ->scalar('information_md_interconnect_bandwidth')
            ->maxLength('information_md_interconnect_bandwidth', 25)
            ->allowEmptyString('information_md_interconnect_bandwidth');

        $validator
            ->scalar('information_client_volatile_memory_capacity')
            ->maxLength('information_client_volatile_memory_capacity', 255)
            ->requirePresence('information_client_volatile_memory_capacity', 'create')
            ->notEmptyString('information_client_volatile_memory_capacity');

        $validator
            ->scalar('information_client_clock')
            ->maxLength('information_client_clock', 25)
            ->requirePresence('information_client_clock', 'create')
            ->notEmptyString('information_client_clock');

        $validator
            ->scalar('information_ds_media_secondary_interface')
            ->maxLength('information_ds_media_secondary_interface', 255)
            ->allowEmptyString('information_ds_media_secondary_interface');

        $validator
            ->scalar('information_md_clock')
            ->maxLength('information_md_clock', 25)
            ->allowEmptyString('information_md_clock');

        $validator
            ->integer('information_ds_media_primary_count')
            ->allowEmptyString('information_ds_media_primary_count');

        $validator
            ->scalar('information_client_architecture')
            ->maxLength('information_client_architecture', 255)
            ->requirePresence('information_client_architecture', 'create')
            ->notEmptyString('information_client_architecture');

        $validator
            ->boolean('information_client_interconnect_rdma')
            ->requirePresence('information_client_interconnect_rdma', 'create')
            ->notEmptyString('information_client_interconnect_rdma');

        $validator
            ->scalar('information_md_media_primary_type')
            ->maxLength('information_md_media_primary_type', 255)
            ->allowEmptyString('information_md_media_primary_type');

        $validator
            ->scalar('information_md_model')
            ->maxLength('information_md_model', 255)
            ->allowEmptyString('information_md_model');

        $validator
            ->scalar('information_ds_interconnect_bandwidth')
            ->maxLength('information_ds_interconnect_bandwidth', 25)
            ->allowEmptyString('information_ds_interconnect_bandwidth');

        $validator
            ->scalar('information_md_operating_system')
            ->maxLength('information_md_operating_system', 255)
            ->allowEmptyString('information_md_operating_system');

        $validator
            ->boolean('information_ds_dpdk')
            ->allowEmptyString('information_ds_dpdk');

        $validator
            ->scalar('information_client_interconnect_bandwidth')
            ->maxLength('information_client_interconnect_bandwidth', 25)
            ->allowEmptyString('information_client_interconnect_bandwidth');

        $validator
            ->scalar('information_ds_media_primary_interface')
            ->maxLength('information_ds_media_primary_interface', 255)
            ->allowEmptyString('information_ds_media_primary_interface');

        $validator
            ->integer('information_md_cores_per_socket')
            ->allowEmptyString('information_md_cores_per_socket');

        $validator
            ->scalar('information_ds_media_primary_capacity')
            ->maxLength('information_ds_media_primary_capacity', 25)
            ->allowEmptyString('information_ds_media_primary_capacity');

        $validator
            ->scalar('information_md_media_secondary_type')
            ->maxLength('information_md_media_secondary_type', 255)
            ->allowEmptyString('information_md_media_secondary_type');

        $validator
            ->scalar('information_ds_media_secondary_type')
            ->maxLength('information_ds_media_secondary_type', 255)
            ->allowEmptyString('information_ds_media_secondary_type');

        $validator
            ->scalar('information_md_media_primary_vendor')
            ->maxLength('information_md_media_primary_vendor', 255)
            ->allowEmptyString('information_md_media_primary_vendor');

        $validator
            ->scalar('information_md_media_primary_interface')
            ->maxLength('information_md_media_primary_interface', 255)
            ->allowEmptyString('information_md_media_primary_interface');

        $validator
            ->scalar('information_md_media_primary_capacity')
            ->maxLength('information_md_media_primary_capacity', 25)
            ->allowEmptyString('information_md_media_primary_capacity');

        $validator
            ->scalar('information_md_interconnect_type')
            ->maxLength('information_md_interconnect_type', 255)
            ->allowEmptyString('information_md_interconnect_type');

        $validator
            ->scalar('information_ds_media_primary_type')
            ->maxLength('information_ds_media_primary_type', 255)
            ->allowEmptyString('information_ds_media_primary_type');

        $validator
            ->integer('information_md_sockets')
            ->allowEmptyString('information_md_sockets');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['id']), ['errorField' => 'id']);
        $rules->add($rules->existsIn(['release_id'], 'Releases'), ['errorField' => 'release_id']);
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }
}
