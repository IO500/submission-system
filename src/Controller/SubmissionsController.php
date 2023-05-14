<?php
declare(strict_types=1);

use Archive\Tar;

namespace App\Controller;

/**
 * Submissions Controller
 *
 * @property \App\Model\Table\SubmissionsTable $Submissions
 * @method \App\Model\Entity\Submission[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class SubmissionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->paginate = [
            'contain' => [
                'Releases',
                'Questionnaires',
                'Status',
            ],
            'order' => [
                'Submissions.id' => 'DESC',
            ],
        ];

        $submissions = $this->paginate($this->Submissions);

        $this->set(compact('submissions'));
    }

    /**
     * Mine method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function mine()
    {
        $userID = $this->getRequest()->getAttribute('identity')['id'] ?? null;

        $query = $this->Submissions->find('all')
            ->where([
                'Submissions.user_id' => $userID,
            ]);

        $this->paginate = [
            'contain' => [
                'Releases',
                'Status',
            ],
            'order' => [
                'id' => 'DESC',
            ],
        ];
        $submissions = $this->paginate($query);

        $this->set(compact('submissions'));
    }

    /**
     * View method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings',
                'Users',
            ],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $submission = $this->Submissions->patchEntity($submission, $data);

            if ($this->Submissions->save($submission)) {
                $this->Flash->success(__('The submission status has been updated.'));

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error(__('The submission status could not be updated. Please, try again.'));
            }
        }

        $status = $this->Submissions->Status->find('list', ['limit' => 200]);

        $this->set(compact('submission', 'status'));
    }

    /**
     * Confirmation method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function confirmation($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings',
                'Status',
            ],
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $submission = $this->Submissions->patchEntity($submission, $data);

            if ($data['confirmation']) {
                $submission->status_id = 2;
            }

            if ($this->Submissions->save($submission)) {
                $this->Flash->success(__('Thank you! Your submission is now under review.'));

                return $this->redirect(['action' => 'mine']);
            }

            $this->Flash->error(__('The submission could not be saved. Please, try again.'));
        }

        $this->set(compact('submission'));
    }

    /**
     * parse method
     *
     * @param object|null $submission Submission.
     * @param object|null $json JSON array with information.
     * @return object|null $submission Submission.
     */
    private function parse($submission, $json)
    {
        // Institution
        $json_site = $this->find_information($json, 'type', 'SITE');

        $submission->information_institution = $json_site['att']['institution'];

        // debug($json_site);

        // Supercomputer

        $json_supercomputer = $this->find_information($json_site, 'type', 'SUPERCOMPUTER');

        $submission->information_system = $json_supercomputer['att']['name'] ?? null;

        // IO500

        $json_io500 = $this->find_information($json_site, 'type', 'IO500');

        $submission->information_client_nodes = $json_io500['att']['number_clientNodes'] ?? 0;
        $submission->information_client_procs_per_node = $json_io500['att']['procsPerNode'] ?? 0;
        $submission->information_client_total_procs = $submission->information_client_nodes * $submission->information_client_procs_per_node;

        if ($submission->information_client_nodes == 10) {
            $submission->information_10_node_challenge = true;
        }

        $submission->information_note = $json_io500['att']['note'] ?? 0;

        // Client Nodes

        $json_client = $this->find_information($json_supercomputer, 'type', 'NODES');

        $submission->information_client_nodes = $json_client['att']['count'] ?? null;
        $submission->information_client_operating_system = $json_client['att']['distribution'] ?? null;
        $submission->information_client_operating_system_version = $json_client['att']['distribution version'] ?? null;
        $submission->information_client_kernel_version = $json_client['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_client_processor = $this->find_information($json_client, 'type', 'PROCESSOR')['att'];

        $submission->information_client_architecture = $json_client_processor['architecture'] ?? null;
        $submission->information_client_model = $json_client_processor['model'] ?? null;
        $submission->information_client_sockets = $json_client_processor['sockets'] ?? null;
        $submission->information_client_cores_per_socket = $json_client_processor['cores per socket'] ?? null;
        $submission->information_client_clock = isset($json_client_processor['frequency']) ? implode(' ', $json_client_processor['frequency']) : null;

        $json_client_memory = $this->find_information($json_client, 'type', 'MEMORY')['att'];

        $submission->information_client_volatile_memory_capacity = isset($json_client_memory['net capacity']) ? implode(' ', $json_client_memory['net capacity']) : null;

        $json_client_interconnect = $this->find_information($json_client, 'type', 'INTERCONNECT')['att'];

        $submission->information_client_interconnect_type = $json_client_interconnect['type'] ?? null;
        $submission->information_client_interconnect_vendor = $json_client_interconnect['vendor'] ?? null;
        $submission->information_client_interconnect_bandwidth = isset($json_client_interconnect['peak throughput']) ? implode(' ', $json_client_interconnect['peak throughput']) : null;
        $submission->information_client_interconnect_links = $json_client_interconnect['links'] ?? null;
        $submission->information_client_interconnect_rdma = isset($json_client_interconnect['features']) ? (strpos($json_client_interconnect['features'], 'RDMA') === false ? false : true) : false;

        $json_storage_system = $this->find_information($json, 'type', 'STORAGESYSTEM');

        $submission->information_filesystem_type = $json_storage_system['att']['software'] ?? null;
        $submission->information_filesystem_name = $json_storage_system['att']['name'] ?? null;
        $submission->information_filesystem_version = $json_storage_system['att']['version'] ?? null;

        $submission->information_storage_vendor = $json_storage_system['att']['vendor'] ?? null;

        $submission->information_client_spdk = isset($json_storage_system['att']['frameworks']) ? (strpos($json_storage_system['att']['frameworks'], 'SPDK') === false ? false : true) : false;
        $submission->information_client_dpdk = isset($json_storage_system['att']['frameworks']) ? (strpos($json_storage_system['att']['frameworks'], 'DPDK') === false ? false : true) : false;

        // LUSTRE
        $json_lustre = $this->find_information($json_storage_system, 'type', 'LUSTRE');

        if ($json_lustre) {
            $submission = $this->parse_lustre($submission, $json_lustre);
        }

        // SPECTRUMSCALE
        $json_spectrum = $this->find_information($json_storage_system, 'type', 'SPECTRUMSCALE');

        if ($json_spectrum) {
            $submission = $this->parse_spectrum($submission, $json_spectrum);
        }

        // BEEGFS
        $json_beegfs = $this->find_information($json_storage_system, 'type', 'BEEGFS');

        if ($json_beegfs) {
            $submission = $this->parse_beegfs($submission, $json_beegfs);
        }

        // NAS
        $json_nas = $this->find_information($json_storage_system, 'type', 'NAS');

        if ($json_nas) {
            $submission = $this->parse_nas($submission, $json_nas);
        }

        // DAOS
        $json_daos = $this->find_information($json_storage_system, 'type', 'DAOS');

        if ($json_daos) {
            $submission = $this->parse_daos($submission, $json_daos);
        }

        // WEKAIO
        $json_wekaio = $this->find_information($json_storage_system, 'type', 'WEKAIO');

        if ($json_wekaio) {
            $submission = $this->parse_daos($submission, $json_wekaio);
        }

        return $submission;
    }

    /**
     * parse_lustre method
     *
     * @param object|null $submission Submission.
     * @param object|null $json_lustre JSON array with LUSTRE information.
     * @return object|null $submission Submission.
     */
    private function parse_lustre($submission, $json_lustre)
    {
        $submission->information_ds_software_version = $json_lustre_server['att']['version'] ?? null;
        $submission->information_md_software_version = $json_lustre_server['att']['version'] ?? null;

        // Data Server
        $json_lustre_server = $this->find_information($json_lustre, 'type', 'OSS');

        $submission->information_ds_nodes = $json_lustre_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_lustre_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_lustre_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_lustre_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_lustre_server_processor = $this->find_information($json_lustre_server, 'type', 'PROCESSOR');

        if ($json_lustre_server_processor) {
            $submission->information_ds_architecture = $json_lustre_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_lustre_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_lustre_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_lustre_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_lustre_server_processor['att']['frequency']) ? implode(' ', $json_lustre_server_processor['att']['frequency']) : null;
        }

        $json_lustre_server_memory = $this->find_information($json_lustre_server, 'type', 'MEMORY');

        if ($json_lustre_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_lustre_server_memory['att']['net capacity']) ? implode(' ', $json_lustre_server_memory['att']['net capacity']) : null;
        }

        $json_lustre_server_interconnect = $this->find_information($json_lustre_server, 'type', 'INTERCONNECT');

        if ($json_lustre_server_interconnect) {
            $submission->information_ds_network = $json_lustre_server_media['att']['count'] ?? null; // equals to information_ds_interconnect_type
            $submission->information_ds_interconnect_type = $json_lustre_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_lustre_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_lustre_server_interconnect['att']['peak throughput']) ? implode(' ', $json_lustre_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_lustre_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_lustre_server_interconnect['att']['features']) ? (strpos($json_lustre_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        $json_lustre_server_media = $this->find_information($json_lustre_server, 'type', 'STORAGEMEDIA');

        if ($json_lustre_server_media) {
            $submission->information_ds_storage_type = $json_lustre_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_lustre_server_media['att']['interface'] ?? null;
        }

        // Metadata Server
        $json_lustre_server = $this->find_information($json_lustre, 'type', 'MDS');

        $submission->information_md_nodes = $json_lustre_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_lustre_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_lustre_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_lustre_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_lustre_server_processor = $this->find_information($json_lustre_server, 'type', 'PROCESSOR')['att'];

        if ($json_lustre_server_processor) {
            $submission->information_md_architecture = $json_lustre_server_processor['architecture'] ?? null;
            $submission->information_md_model = $json_lustre_server_processor['model'] ?? null;
            $submission->information_md_sockets = $json_lustre_server_processor['sockets'] ?? null;
            $submission->information_md_cores_per_socket = $json_lustre_server_processor['cores per socket'] ?? null;
            $submission->information_md_clock = isset($json_lustre_server_processor['frequency']) ? implode(' ', $json_lustre_server_processor['frequency']) : null;
        }

        $json_lustre_server_memory = $this->find_information($json_lustre_server, 'type', 'MEMORY')['att'];

        if ($json_lustre_server_memory) {
            $submission->information_md_volatile_memory_capacity = isset($json_lustre_server_memory['net capacity']) ? implode(' ', $json_lustre_server_memory['net capacity']) : null;
        }

        $json_lustre_server_interconnect = $this->find_information($json_lustre_server, 'type', 'INTERCONNECT')['att'];

        if ($json_lustre_server_interconnect) {
            $submission->information_md_interconnect_type = $json_lustre_server_interconnect['type'] ?? null; // same as information_md_network
            $submission->information_md_interconnect_vendor = $json_lustre_server_interconnect['vendor'] ?? null;
            $submission->information_md_interconnect_bandwidth = isset($json_lustre_server_interconnect['peak throughput']) ? implode(' ', $json_lustre_server_interconnect['peak throughput']) : null;
            $submission->information_md_interconnect_links = $json_lustre_server_interconnect['links'] ?? null;
            $submission->information_md_interconnect_rdma = isset($json_lustre_server_interconnect['features']) ? (strpos($json_lustre_server_interconnect['features'], 'RDMA') === false ? false : true) : false;
        }

        $json_lustre_server_media = $this->find_information($json_lustre_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_lustre_server_media) {
            $submission->information_md_media_primary_type = $json_lustre_server_media['att']['type'] ?? null; // same as information_md_storage_type
            $submission->information_md_media_primary_vendor = $json_lustre_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_lustre_server_media['att']['interface'] ?? null; // same as information_md_storage_interface
            $submission->information_md_media_primary_count = $json_lustre_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_lustre_server_media['att']['net capacity']) ? implode(' ', $json_lustre_server_media['att']['net capacity']) : null;
        }

        $json_lustre_server_media = $this->find_information($json_lustre_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_lustre_server_media) {
            $submission->information_md_media_secondary_type = $json_lustre_server_media['att']['type'] ?? null;
            $submission->information_md_media_secondary_vendor = $json_lustre_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_lustre_server_media['att']['interface'] ?? null;
            $submission->information_md_media_secondary_count = $json_lustre_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_lustre_server_media['att']['net capacity']) ? implode(' ', $json_lustre_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    /**
     * parse_spectrum method
     *
     * @param object|null $submission Submission.
     * @param object|null $json_spectrum JSON array with SPECTRUM information.
     * @return object|null $submission Submission.
     */
    private function parse_spectrum($submission, $json_spectrum)
    {
        $submission->information_ds_software_version = $json_spectrum_server['att']['version'] ?? null;
        $submission->information_md_software_version = $json_spectrum_server['att']['version'] ?? null;

        // Data Server
        $json_spectrum_server = $this->find_information($json_spectrum, 'type', 'DATA SERVERS');
        $json_spectrum_server = $this->find_information($json_spectrum_server, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_spectrum_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_spectrum_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_spectrum_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_spectrum_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_spectrum_server_processor = $this->find_information($json_spectrum_server, 'type', 'PROCESSOR');

        if ($json_spectrum_server_processor) {
            $submission->information_ds_architecture = $json_spectrum_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_spectrum_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_spectrum_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_spectrum_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_spectrum_server_processor['att']['frequency']) ? implode(' ', $json_spectrum_server_processor['att']['frequency']) : null;
        }

        $json_spectrum_server_memory = $this->find_information($json_spectrum_server, 'type', 'MEMORY');

        if ($json_spectrum_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_spectrum_server_memory['att']['net capacity']) ? implode(' ', $json_spectrum_server_memory['att']['net capacity']) : null;
        }

        $json_spectrum_server_interconnect = $this->find_information($json_spectrum_server, 'type', 'INTERCONNECT');

        if ($json_spectrum_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_spectrum_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_spectrum_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_spectrum_server_interconnect['att']['peak throughput']) ? implode(' ', $json_spectrum_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_spectrum_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_spectrum_server_interconnect['att']['features']) ? (strpos($json_spectrum_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        $json_spectrum_server_media = $this->find_information($json_spectrum_server, 'type', 'STORAGEMEDIA');

        if ($json_spectrum_server_media) {
            $submission->information_ds_storage_type = $json_spectrum_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_spectrum_server_media['att']['interface'] ?? null;
        }

        // Metadata Server
        $json_spectrum_server = $this->find_information($json_spectrum, 'type', 'METADATA SERVERS');
        $json_spectrum_server = $this->find_information($json_spectrum_server, 'type', 'SERVERS');

        $submission->information_md_nodes = $json_spectrum_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_spectrum_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_spectrum_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_spectrum_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_spectrum_server_processor = $this->find_information($json_spectrum_server, 'type', 'PROCESSOR')['att'];

        $submission->information_md_architecture = $json_spectrum_server_processor['architecture'] ?? null;
        $submission->information_md_model = $json_spectrum_server_processor['model'] ?? null;
        $submission->information_md_sockets = $json_spectrum_server_processor['sockets'] ?? null;
        $submission->information_md_cores_per_socket = $json_spectrum_server_processor['cores per socket'] ?? null;
        $submission->information_md_clock = isset($json_spectrum_server_processor['frequency']) ? implode(' ', $json_spectrum_server_processor['frequency']) : null;

        $json_spectrum_server_memory = $this->find_information($json_spectrum_server, 'type', 'MEMORY')['att'];

        $submission->information_md_volatile_memory_capacity = isset($json_spectrum_server_memory['net capacity']) ? implode(' ', $json_spectrum_server_memory['net capacity']) : null;

        $json_spectrum_server_interconnect = $this->find_information($json_spectrum_server, 'type', 'INTERCONNECT')['att'];

        $submission->information_md_interconnect_type = $json_spectrum_server_interconnect['type'] ?? null;
        $submission->information_md_interconnect_vendor = $json_spectrum_server_interconnect['vendor'] ?? null;
        $submission->information_md_interconnect_bandwidth = isset($json_spectrum_server_interconnect['peak throughput']) ? implode(' ', $json_spectrum_server_interconnect['peak throughput']) : null;
        $submission->information_md_interconnect_links = $json_spectrum_server_interconnect['links'] ?? null;
        $submission->information_md_interconnect_rdma = isset($json_spectrum_server_interconnect['features']) ? (strpos($json_spectrum_server_interconnect['features'], 'RDMA') === false ? false : true) : false;

        $json_spectrum_server_media = $this->find_information($json_spectrum_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_spectrum_server_media) {
            $submission->information_md_media_primary_type = $json_spectrum_server_media['att']['type'] ?? null;
            $submission->information_md_media_primary_vendor = $json_spectrum_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_spectrum_server_media['att']['interface'] ?? null;
            $submission->information_md_media_primary_count = $json_spectrum_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_spectrum_server_media['att']['net capacity']) ? implode(' ', $json_spectrum_server_media['att']['net capacity']) : null;
        }

        $json_spectrum_server_media = $this->find_information($json_spectrum_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_spectrum_server_media) {
            $submission->information_md_media_secondary_type = $json_spectrum_server_media['att']['type'] ?? null; // same as information_md_storage_type
            $submission->information_md_media_secondary_vendor = $json_spectrum_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_spectrum_server_media['att']['interface'] ?? null; // same as information_md_storage_interface
            $submission->information_md_media_secondary_count = $json_spectrum_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_spectrum_server_media['att']['net capacity']) ? implode(' ', $json_spectrum_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    /**
     * parse_beegfs method
     *
     * @param object|null $submission Submission.
     * @param object|null $json_beegfs JSON array with BEEGFS information.
     * @return object|null $submission Submission.
     */
    private function parse_beegfs($submission, $json_beegfs)
    {
        $submission->information_ds_software_version = $json_beegfs_server['att']['version'] ?? null;
        $submission->information_md_software_version = $json_beegfs_server['att']['version'] ?? null;

        // Data Server
        $json_beegfs_server = $this->find_information($json_beegfs, 'type', 'STORAGE SERVER');

        $submission->information_ds_nodes = $json_beegfs_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_beegfs_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_beegfs_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_beegfs_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_beegfs_server_processor = $this->find_information($json_beegfs_server, 'type', 'PROCESSOR');

        if ($json_beegfs_server_processor) {
            $submission->information_ds_architecture = $json_beegfs_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_beegfs_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_beegfs_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_beegfs_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_beegfs_server_processor['att']['frequency']) ? implode(' ', $json_beegfs_server_processor['att']['frequency']) : null;
        }

        $json_beegfs_server_memory = $this->find_information($json_beegfs_server, 'type', 'MEMORY');

        if ($json_beegfs_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_beegfs_server_memory['att']['net capacity']) ? implode(' ', $json_beegfs_server_memory['att']['net capacity']) : null;
        }

        $json_beegfs_server_interconnect = $this->find_information($json_beegfs_server, 'type', 'INTERCONNECT');

        if ($json_beegfs_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_beegfs_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_beegfs_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_beegfs_server_interconnect['att']['peak throughput']) ? implode(' ', $json_beegfs_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_beegfs_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_beegfs_server_interconnect['att']['features']) ? (strpos($json_beegfs_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        $json_beegfs_server_media = $this->find_information($json_beegfs_server, 'type', 'STORAGEMEDIA');

        if ($json_beegfs_server_media) {
            $submission->information_ds_storage_type = $json_beegfs_server_media['att']['type'] ?? null;
            $submission->information_ds_storage_interface = $json_beegfs_server_media['att']['interface'] ?? null;
        }

        // Metadata Server
        $json_beegfs_server = $this->find_information($json_beegfs, 'type', 'METADATA SERVER');

        $submission->information_md_nodes = $json_beegfs_server['att']['count'] ?? null;
        $submission->information_md_operating_system = $json_beegfs_server['att']['distribution'] ?? null;
        $submission->information_md_operating_system_version = $json_beegfs_server['att']['distribution version'] ?? null;
        $submission->information_md_kernel_version = $json_beegfs_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_beegfs_server_processor = $this->find_information($json_beegfs_server, 'type', 'PROCESSOR')['att'];

        if ($json_beegfs_server_processor) {
            $submission->information_md_architecture = $json_beegfs_server_processor['architecture'] ?? null;
            $submission->information_md_model = $json_beegfs_server_processor['model'] ?? null;
            $submission->information_md_sockets = $json_beegfs_server_processor['sockets'] ?? null;
            $submission->information_md_cores_per_socket = $json_beegfs_server_processor['cores per socket'] ?? null;
            $submission->information_md_clock = isset($json_beegfs_server_processor['frequency']) ? implode(' ', $json_beegfs_server_processor['frequency']) : null;
        }

        $json_beegfs_server_memory = $this->find_information($json_beegfs_server, 'type', 'MEMORY')['att'];

        if ($json_beegfs_server_memory) {
            $submission->information_md_volatile_memory_capacity = isset($json_beegfs_server_memory['net capacity']) ? implode(' ', $json_beegfs_server_memory['net capacity']) : null;
        }

        $json_beegfs_server_interconnect = $this->find_information($json_beegfs_server, 'type', 'INTERCONNECT')['att'];

        if ($json_beegfs_server_interconnect) {
            $submission->information_md_interconnect_type = $json_beegfs_server_interconnect['type'] ?? null;
            $submission->information_md_interconnect_vendor = $json_beegfs_server_interconnect['vendor'] ?? null;
            $submission->information_md_interconnect_bandwidth = isset($json_beegfs_server_interconnect['peak throughput']) ? implode(' ', $json_beegfs_server_interconnect['peak throughput']) : null;
            $submission->information_md_interconnect_links = $json_beegfs_server_interconnect['links'] ?? null;
            $submission->information_md_interconnect_rdma = isset($json_beegfs_server_interconnect['features']) ? (strpos($json_beegfs_server_interconnect['features'], 'RDMA') === false ? false : true) : false;
        }

        $json_beegfs_server_media = $this->find_information($json_beegfs_server, 'type', 'STORAGEMEDIA', 1);

        if ($json_beegfs_server_media) {
            $submission->information_md_media_primary_type = $json_beegfs_server_media['att']['type'] ?? null;
            $submission->information_md_media_primary_vendor = $json_beegfs_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_primary_interface = $json_beegfs_server_media['att']['interface'] ?? null;
            $submission->information_md_media_primary_count = $json_beegfs_server_media['att']['count'] ?? null;
            $submission->information_md_media_primary_capacity = isset($json_beegfs_server_media['att']['net capacity']) ? implode(' ', $json_beegfs_server_media['att']['net capacity']) : null;
        }

        $json_beegfs_server_media = $this->find_information($json_beegfs_server, 'type', 'STORAGEMEDIA', 2);

        if ($json_beegfs_server_media) {
            $submission->information_md_media_secondary_type = $json_beegfs_server_media['att']['type'] ?? null;
            $submission->information_md_media_secondary_vendor = $json_beegfs_server_media['att']['vendor'] ?? null;
            $submission->information_md_media_secondary_interface = $json_beegfs_server_media['att']['interface'] ?? null;
            $submission->information_md_media_secondary_count = $json_beegfs_server_media['att']['count'] ?? null;
            $submission->information_md_media_secondary_capacity = isset($json_beegfs_server_media['att']['net capacity']) ? implode(' ', $json_beegfs_server_media['att']['net capacity']) : null;
        }

        return $submission;
    }

    /**
     * parse_nas method
     *
     * @param object|null $submission Submission.
     * @param object|null $json_nas JSON array with NAS information.
     * @return object|null $submission Submission.
     */
    private function parse_nas($submission, $json_nas)
    {
        $submission->information_ds_software_version = $json_nas_server['att']['version'] ?? null;

        // Data Server
        $json_nas_server = $this->find_information($json_nas, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_nas_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_nas_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_nas_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_nas_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_nas_server_processor = $this->find_information($json_nas_server, 'type', 'PROCESSOR');

        if ($json_nas_server_processor) {
            $submission->information_ds_architecture = $json_nas_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_nas_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_nas_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_nas_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_nas_server_processor['att']['frequency']) ? implode(' ', $json_nas_server_processor['att']['frequency']) : null;
        }

        $json_nas_server_memory = $this->find_information($json_nas_server, 'type', 'MEMORY');

        if ($json_nas_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_nas_server_memory['att']['net capacity']) ? implode(' ', $json_nas_server_memory['att']['net capacity']) : null;
        }

        $json_nas_server_interconnect = $this->find_information($json_nas_server, 'type', 'INTERCONNECT');

        if ($json_nas_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_nas_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_nas_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_nas_server_interconnect['att']['peak throughput']) ? implode(' ', $json_nas_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_nas_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_nas_server_interconnect['att']['features']) ? (strpos($json_nas_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        return $submission;
    }

    /**
     * parse_daos method
     *
     * @param object|null $submission Submission.
     * @param object|null $json_daos JSON array with DAOS information.
     * @return object|null $submission Submission.
     */
    private function parse_daos($submission, $json_daos)
    {
        $submission->information_ds_software_version = $json_daos['att']['version'] ?? null;

        // Data Server
        $json_daos_server = $this->find_information($json_daos, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_daos_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_daos_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_daos_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_daos_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_daos_server_processor = $this->find_information($json_daos_server, 'type', 'PROCESSOR');

        if ($json_daos_server_processor) {
            $submission->information_ds_architecture = $json_daos_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_daos_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_daos_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_daos_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_daos_server_processor['att']['frequency']) ? implode(' ', $json_daos_server_processor['att']['frequency']) : null;
        }

        $json_daos_server_memory = $this->find_information($json_daos_server, 'type', 'MEMORY');

        if ($json_daos_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_daos_server_memory['att']['net capacity']) ? implode(' ', $json_daos_server_memory['att']['net capacity']) : null;
        }

        $json_daos_server_interconnect = $this->find_information($json_daos_server, 'type', 'INTERCONNECT');

        if ($json_daos_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_daos_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_daos_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_daos_server_interconnect['att']['peak throughput']) ? implode(' ', $json_daos_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_daos_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_daos_server_interconnect['att']['features']) ? (strpos($json_daos_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        return $submission;
    }

    /**
     * parse_wekaio method
     *
     * @param object|null $submission Submission.
     * @param object|null $json_wekaio JSON array with WEKAIO information.
     * @return object|null $submission Submission.
     */
    private function parse_wekaio($submission, $json_wekaio)
    {
        $submission->information_ds_software_version = $json_wekaio['att']['version'] ?? null;

        // Data Server
        $json_wekaio_server = $this->find_information($json_wekaio, 'type', 'SERVERS');

        $submission->information_ds_nodes = $json_wekaio_server['att']['count'] ?? null;
        $submission->information_ds_operating_system = $json_wekaio_server['att']['distribution'] ?? null;
        $submission->information_ds_operating_system_version = $json_wekaio_server['att']['distribution version'] ?? null;
        $submission->information_ds_kernel_version = $json_wekaio_server['att']['kernel version'] ?? null;

        $submission->information_submission_date = $submission->information_submission_date ?? date('Y-m-d H:i:s');

        $json_wekaio_server_processor = $this->find_information($json_wekaio_server, 'type', 'PROCESSOR');

        if ($json_wekaio_server_processor) {
            $submission->information_ds_architecture = $json_wekaio_server_processor['att']['architecture'] ?? null;
            $submission->information_ds_model = $json_wekaio_server_processor['att']['model'] ?? null;
            $submission->information_ds_sockets = $json_wekaio_server_processor['att']['sockets'] ?? null;
            $submission->information_ds_cores_per_socket = $json_wekaio_server_processor['att']['cores per socket'] ?? null;
            $submission->information_ds_clock = isset($json_wekaio_server_processor['att']['frequency']) ? implode(' ', $json_wekaio_server_processor['att']['frequency']) : null;
        }

        $json_wekaio_server_memory = $this->find_information($json_wekaio_server, 'type', 'MEMORY');

        if ($json_wekaio_server_memory) {
            $submission->information_ds_volatile_memory_capacity = isset($json_wekaio_server_memory['att']['net capacity']) ? implode(' ', $json_wekaio_server_memory['att']['net capacity']) : null;
        }

        $json_wekaio_server_interconnect = $this->find_information($json_wekaio_server, 'type', 'INTERCONNECT');

        if ($json_wekaio_server_interconnect) {
            $submission->information_ds_interconnect_type = $json_wekaio_server_interconnect['att']['type'] ?? null;
            $submission->information_ds_interconnect_vendor = $json_wekaio_server_interconnect['att']['vendor'] ?? null;
            $submission->information_ds_interconnect_bandwidth = isset($json_wekaio_server_interconnect['att']['peak throughput']) ? implode(' ', $json_wekaio_server_interconnect['att']['peak throughput']) : null;
            $submission->information_ds_interconnect_links = $json_wekaio_server_interconnect['att']['links'] ?? null;
            $submission->information_ds_interconnect_rdma = isset($json_wekaio_server_interconnect['att']['features']) ? (strpos($json_wekaio_server_interconnect['att']['features'], 'RDMA') === false ? false : true) : false;
        }

        return $submission;
    }

    /**
     * Metrics method
     *
     * @param object|null $submission Submission.
     * @return object|null $submission Submission.
     */
    private function metrics($submission)
    {
        // IO500 metrics
        /* if (strpos($submission->result_tar_type, 'zip') !== false) {
            try {
                $zip = new \ZipArchive();

                if (!is_file(ROOT . DS . $submission->result_tar_dir . $submission->result_tar)) {
                    return null;
                }

                if ($zip->open(ROOT . DS . $submission->result_tar_dir . $submission->result_tar, \ZipArchive::RDONLY) == true) {
                    $result_file = null;

                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $filename = $zip->getNameIndex($i);

                        if (basename($filename) == 'result_summary.txt') {
                            $result_file = $filename;
                        }
                    }

                    $content = $zip->getFromName($result_file);

                    $zip->close();

                    $re = '/\[.*\]\s*(\S*)\s*(\S*)\s*(\S*)\s*:\s*time\s*(\S*)\s*seconds/m';

                    preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);

                    $results = [];

                    foreach ($matches as $match) {
                        if ($match[1] == 'timestamp') {
                            continue;
                        }

                        $results[$match[1]] = $match[2];
                    }

                    $re = '/\[.*\]\s*(\S*)\s*(\S*)\s*(\S*)\s*:\s*(\S*)\s*(\S*)\s*(\S*)\s*:\s*(\S*)\s*(\S*)/m';

                    preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);

                    $match = $matches[0];

                    $total = [];

                    $total[$match[1]] = $match[2];
                    $total[$match[4]] = $match[5];
                    $total[$match[7]] = $match[8];

                    $submission->io500_bw = $total['Bandwidth'];
                    $submission->io500_md = $total['IOPS'];
                    $submission->io500_score = $total['TOTAL'];

                    $submission->ior_easy_write = $results['ior-easy-write'] ?? null;
                    $submission->ior_easy_read = $results['ior-easy-read'] ?? null;
                    $submission->ior_hard_write = $results['ior-hard-write'] ?? null;
                    $submission->ior_hard_read = $results['ior-hard-read'] ?? null;

                    $submission->mdtest_easy_write = $results['mdtest-easy-write'] ?? null;
                    $submission->mdtest_easy_stat = $results['mdtest-easy-stat'] ?? null;
                    $submission->mdtest_easy_delete = $results['mdtest-easy-delete'] ?? null;
                    $submission->mdtest_hard_write = $results['mdtest-hard-write'] ?? null;
                    $submission->mdtest_hard_stat = $results['mdtest-hard-stat'] ?? null;
                    $submission->mdtest_hard_read = $results['mdtest-hard-read'] ?? null;
                    $submission->mdtest_hard_delete = $results['mdtest-hard-delete'] ?? null;

                    $submission->find_mixed = $results['find'] ?? null;

                    return $submission;
                } else {
                    return null;
                }
            } catch (\Exception $e) {
                return null;
            }
        } */

        //if (strpos($submission->result_tar_type, 'application/x-compressed-tar') !== false) {
        try {
            if (!is_file(ROOT . DS . $submission->result_tar_dir . $submission->result_tar)) {
                return null;
            }

            $fh = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator('phar://' . ROOT . DS . $submission->result_tar_dir . $submission->result_tar),
                \RecursiveIteratorIterator::CHILD_FIRST
            );

            $result_file;

            foreach ($fh as $splFileInfo) {
                if (basename($splFileInfo->getPathname()) == 'result_summary.txt') {
                        $result_file = $splFileInfo->getPathname();
                }
            }

            if ($result_file) {
                $content = file_get_contents($result_file);

                $re = '/\[.*\]\s*(\S*)\s*(\S*)\s*(\S*)\s*:\s*time\s*(\S*)\s*seconds/m';

                preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);

                $results = [];

                foreach ($matches as $match) {
                    if ($match[1] == 'timestamp') {
                        continue;
                    }

                    $results[$match[1]] = $match[2];
                }

                $re = '/\[.*\]\s*(\S*)\s*(\S*)\s*(\S*)\s*:\s*(\S*)\s*(\S*)\s*(\S*)\s*:\s*(\S*)\s*(\S*)/m';

                preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);

                $match = $matches[0];

                $total = [];

                $total[$match[1]] = $match[2];
                $total[$match[4]] = $match[5];
                $total[$match[7]] = $match[8];

                $submission->io500_bw = $total['Bandwidth'];
                $submission->io500_md = $total['IOPS'];
                $submission->io500_score = $total['TOTAL'];

                $submission->ior_easy_write = $results['ior-easy-write'] ?? null;
                $submission->ior_easy_read = $results['ior-easy-read'] ?? null;
                $submission->ior_hard_write = $results['ior-hard-write'] ?? null;
                $submission->ior_hard_read = $results['ior-hard-read'] ?? null;

                $submission->mdtest_easy_write = $results['mdtest-easy-write'] ?? null;
                $submission->mdtest_easy_stat = $results['mdtest-easy-stat'] ?? null;
                $submission->mdtest_easy_delete = $results['mdtest-easy-delete'] ?? null;
                $submission->mdtest_hard_write = $results['mdtest-hard-write'] ?? null;
                $submission->mdtest_hard_stat = $results['mdtest-hard-stat'] ?? null;
                $submission->mdtest_hard_read = $results['mdtest-hard-read'] ?? null;
                $submission->mdtest_hard_delete = $results['mdtest-hard-delete'] ?? null;

                $submission->find_mixed = $results['find'] ?? null;

                return $submission;
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $submission = $this->Submissions->newEmptyEntity();

        $release = $this->Submissions->Releases->find('all')
            ->where([
                'Releases.release_date >' => date('Y-m-d'),
            ]);

        if ($release->isEmpty()) {
            $this->Flash->error(__('There are no open calls for submission. Please, check again soon!'));

            return $this->redirect(['action' => 'mine']);
        }

        $release_id = $release->first()['id'];

        if ($this->request->is('post')) {
            #print_r($this->request->getData());
            #print_r(json_encode($this->request->getData()));

            $data = $this->request->getData();

            $submission = $this->Submissions->patchEntity($submission, $data);

            $submission->release_id = $release_id;
            $submission->user_id = $this->getRequest()->getAttribute('identity')['id'];
            $submission->information_submitter = $this->getRequest()->getAttribute('identity')['email'];
            $submission->information_submission_date = date('Y-m-d H:i:s');
            $submission->upload_hash = sha1($submission->user_id . $submission->information_submission_date);

            $submission->status_id = 1;

            // $submission->status = 'NEW';

            $json = json_decode($data['json'], true);

            $submission = $this->parse($submission, $json);

            if ($this->Submissions->save($submission)) {
                //$submission = $this->metrics($submission);
                //$this->Submissions->save($submission);

                file_put_contents(ROOT . DS . 'webroot' . DS . 'files' . DS . 'submissions' . DS . $submission->id . '.json', $data['json']);

                $this->Flash->success(__('The submission has been saved.'));

                return $this->redirect(['action' => 'results', $submission->id]);
            }

            $this->Flash->error(__('The submission could not be saved. Please, try again.'));
        }

        $this->set(compact('submission', 'release_id'));
    }

    /**
     * Results method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function results($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings',
                'Questionnaires',
            ],
        ]);

        // Only allow submissions that are 'new' to be modified. Once released, they should follow the GitHub PR flow.
        if (date('Y-m-d') > $submission->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));

            return $this->redirect(['action' => 'mine']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $submission = $this->Submissions->patchEntity($submission, $data);
            $submission->upload_hash = sha1($submission->user_id . $submission->information_submission_date);
            //$submission = $this->metrics($submission);

            if ($this->Submissions->save($submission)) {
                $submission = $this->metrics($submission);

                if (!$submission) {
                    $this->Flash->error(__('Unable to extract the benchmark results. Please, provide a .zip or .tgz file.'));

                    return $this->redirect(['action' => 'results', $id]);
                }

                $this->Submissions->save($submission);

                $this->Flash->success(__('The submission has been saved.'));

                if ($submission->questionnaire) {
                    return $this->redirect(['controller' => 'questionnaires', 'action' => 'edit', $submission->id]);
                } else {
                    return $this->redirect(['controller' => 'questionnaires', 'action' => 'add', $submission->id]);
                }
            } else {
                $this->Flash->error(__('The submission could not be saved. Please, try again.'));
            }
        }

        $this->set(compact('submission'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings',
            ],
        ]);

        // Only allow submissions that are 'new' to be modified. Once released, they should follow the GitHub PR flow.
        if (date('Y-m-d') > $submission->release->release_date->i18nFormat('yyyy-MM-dd')) {
            $this->Flash->error(__('This submission was already released in a list. To modify its metadata, open a GitHub pull request with the change.'));

            return $this->redirect(['action' => 'mine']);
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            $submission = $this->Submissions->patchEntity($submission, $data);
            $submission->upload_hash = sha1($submission->user_id . $submission->information_submission_date);

            $json = json_decode($data['json'], true);

            $submission = $this->parse($submission, $json);

            if ($this->Submissions->save($submission)) {
                file_put_contents(ROOT . DS . 'webroot' . DS . 'files' . DS . 'submissions' . DS . $submission->id . '.json', $data['json']);

                $this->Flash->success(__('The submission has been saved.'));

                return $this->redirect(['action' => 'results', $submission->id]);
            } else {
                $this->Flash->error(__('The submission could not be saved. Please, try again.'));
            }
        }

        $releases = $this->Submissions->Releases->find('list', ['limit' => 200]);

        $json = ROOT . DS . 'webroot' . DS . 'files' . DS . 'submissions' . DS . $submission->id . '.json';

        if (!is_file($json)) {
            $this->Flash->error(__('Unable to fetch the file in the server.'));

            return $this->redirect(['action' => 'index']);
        }

        $submission->json = file_get_contents($json);

        $this->set(compact('submission', 'releases'));
    }

    /**
     * System method
     *
     * @param string|null $id Submission id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function system($id = null)
    {
        $submission = $this->Submissions->get($id, [
            'contain' => [
                'Releases',
                'Listings',
            ],
        ]);

        $releases = $this->Submissions->Releases->find('list', ['limit' => 200]);

        $json = ROOT . DS . 'webroot' . DS . 'files' . DS . 'submissions' . DS . $submission->id . '.json';

        if (!file_exists($json)) {
            $this->Flash->error(__('Sorry, but this file is not available in the server.'));

            return $this->redirect(['action' => 'index']);
        }

        $submission->json = file_get_contents($json);

        $this->set(compact('submission', 'releases'));
    }

    /**
     * model method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     */
    public function model()
    {
        $data = file_get_contents('php://input');
        $obj = json_decode($data, true);
        $model = '';

        if (strlen($data) == 0 && array_key_exists('type', $_GET)) {
            $type = $_GET['type'];

            if (array_key_exists('model', $_GET)) {
                $model = $_GET['model'];
            }
        } else {
            $model = str_replace('/', '', str_replace(' ', '', $obj['value']));
            $type = $obj['type'];
        }

        $type = strtolower($type);

        if (! preg_match('/^[a-z_]+$/', $type) || ! preg_match("/^[a-zA-Z0-9_\- ]*$/", $model)) {
            echo '{"msg" : "error, invalid character in type or model"}';
            http_response_code(500);
            exit(1);
        }

        $folder = __DIR__ . '/model/' . $type . '/';
        $file = $folder . $model;

        if (array_key_exists('type', $_GET) || ! array_key_exists('data', $obj)) {
            if ($model != '') {
                if (! is_file($file)) {
                    print '{}';

                    exit(0);
                }

                $data = file_get_contents($file);

                if (! $data) {
                    print '{}';
                } else {
                    print $data;
                }

                exit(0);
            }

            if (is_dir($folder)) {
                $files = preg_grep('/^' . $model . '/', scandir($folder));
                $files = array_diff($files, ['.', '..']);

                if ($files) {
                    $out = json_encode(array_values($files));
                } else {
                    $out = '[]';
                }
            } else {
                $out = '[]';
            }

            print '{"complete" : ' . $out . '}';
            exit(0);
        }

        if (!is_dir($folder)) {
            mkdir($folder);
        }

        $ret = file_put_contents($file, json_encode($obj['data'], true));

        if (!$ret) {
            http_response_code(500);
            echo '{"msg" : "Error could not save data! ' . json_encode($obj, true) . '"}';
            die;
        }

        echo '{"msg" : "OK"}';
        exit(0);
    }

    /**
     * Customize method
     * Allows to create custom lists based on the provided list type in the last release
     *
     * @param string $release_acronym Achronym for the release.
     * @param string $type_url URL type.
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function build($release_acronym, $type_url)
    {
        $limit = 1000;

        // Fetch the release based on the provided acronym
        $release = $this->Submissions->Releases->find('all')
            ->where([
                'Releases.acronym' => strtoupper($release_acronym),
            ])
            ->first();

        // Fetch the list type based on the provided URL
        $type = $this->Submissions->Listings->Types->find('all')
            ->where([
                'Types.url' => $type_url,
            ])
            ->first();

        // Fetch the only option for the select box
        $types = $this->Submissions->Listings->Types->find('list')
            ->where([
                'Types.url' => $type_url,
            ]);

        // Find all releases
        $releases = $this->Submissions->Releases->find('list')
            ->where([
                'Releases.release_date >=' => date('Y-m-d'),
            ]);

        $last_release = $this->Submissions->Releases->find('all')
            ->where([
                'Releases.release_date <' => date('Y-m-d'),
            ])
            ->order([
                'Releases.release_date' => 'DESC',
            ])
            ->first();

        // We need the id of the previous released list of the given type to build on it
        $listing = $this->Submissions->Listings->find('all')
            ->contain([
                'Releases',
            ])
            ->where([
                'Listings.type_id' => $type->id,
                'Releases.release_date' => $last_release->release_date->i18nFormat('yyyy-MM-dd'), //date('Y-m-d'),
            ])
            ->order([
                'Releases.release_date' => 'DESC',
            ])
            ->first();

        $novel = false;

        if (empty($listing)) {
            $novel = true;

            // Get the historical list
            $type = $this->Submissions->Listings->Types->find('all')
                ->where([
                    'Types.url' => 'historical',
                ])
                ->first();

            $listing = $this->Submissions->Listings->find('all')
                ->contain([
                    'Releases',
                ])
                ->where([
                    'Listings.type_id' => $type->id,
                    'Releases.release_date <' => $last_release->release_date->i18nFormat('yyyy-MM-dd'), //date('Y-m-d'),
                ])
                ->order([
                    'Releases.release_date' => 'DESC',
                ])
                ->first();

            $this->Flash->warning(__('Unable to find previous releases of this type of list. Fetching all last historical results.'));
        }

        // Fetch all submissions from the previous released list of this given type
        $submissions = $this->Submissions->ListingsSubmissions->find('all')
            ->contain([
                'Submissions' => [
                    'Releases',
                ],
            ])
            ->where([
                'ListingsSubmissions.listing_id' => $listing->id,
            ])
            ->order([
                'ListingsSubmissions.score' => 'DESC',
            ])
            ->limit($limit);

        $records = [];
        $duplicated = [];

        foreach ($submissions as $submission) {
            // We will use the latest valid score to display
            $submission->submission->io500_score = $submission->score;
            $submission->submission->information_list_name = $submission->submission->release->acronym;

            if ($novel) {
                $submission->submission->is_new = true;
            } else {
                $submission->submission->is_new = false;
            }

            // Check for duplicate values
            $key = $submission->submission->information_system . '-' . $submission->submission->information_institution . '-' . $submission->submission->information_filesystem_type;

            if (in_array($key, $duplicated)) {
                $submission->submission->is_duplicated = true;
            }

            $duplicated[] = $key;

            $records[] = $submission->submission;
        }

        // We now need to fecth only the new submissions, i.e. those make between the last list release and today
        $new_submissions = $this->Submissions->find('all')
            ->contain([
                'Releases',
            ])
            ->where([
                'Submissions.information_submission_date >=' => $listing->release->release_date,
            ])
            ->limit($limit);

        foreach ($new_submissions as $submission) {
            // We will use the latest valid score to display
            $submission->is_new = true;

            $key = $submission->information_system . '-' . $submission->information_institution . '-' . $submission->information_filesystem_type;

            if (in_array($key, $duplicated)) {
                $submission->is_duplicated = true;
            }

            $duplicated[] = $key;

            $records[] = $submission;
        }

        // Sort based on the scoree
        uasort($records, [$this, 'sort']);

        $this->set('release_acronym', $release_acronym);
        $this->set('type_url', $type_url);

        $this->set('types', $types);
        $this->set('releases', $releases);
        $this->set('submissions', $records);
    }

    /**
     * Compare two scores method
     *
     * @param  null $a Score A.
     * @param  null $b Score B.
     * @return bool
     */
    private function sort($a, $b)
    {
        return $a->io500_score < $b->io500_score;
    }

    /**
     * Find Information method
     *
     * @param  array $array Submission information.
     * @param  string $key Information key.
     * @param  string $value Information value.
     * @param  int $nth Occurence.
     * @return array|null
     */
    private function find_information($array, $key, $value, $nth = 1)
    {
        if (is_array($array)) {
            $iterator = new \RecursiveArrayIterator($array);
            $recursive = new \RecursiveIteratorIterator(
                $iterator,
                \RecursiveIteratorIterator::SELF_FIRST
            );

            $n = 1;

            foreach ($recursive as $k => $v) {
                if ($k === $key && strtolower($v) == strtolower($value)) {
                    if ($n == $nth) {
                        return $recursive->getSubIterator($recursive->getDepth() - 1)->current();
                    }

                    $n++;
                }
            }
        }

        return null;
    }
}
