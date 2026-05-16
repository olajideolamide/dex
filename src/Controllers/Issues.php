<?php

/**
 * This file is part of Dex.
 *
 * (c) Olajide Olanrewaju <jidemac4@gmail.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Dex\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Dex\Domain\Exceptions\IssueNotFoundException;
use Dex\Domain\Exceptions\DexException;
use Dex\Orchestrators\IssuesOrchestrator;
use Dex\Config\Services as DexServices;
use InvalidArgumentException;

class Issues extends DexController
{
    private IssuesOrchestrator $orchestrator;

    public function __construct(?IssuesOrchestrator $orchestrator = null)
    {
        $this->orchestrator = $orchestrator ?? DexServices::issuesOrchestrator(false);
    }

    public function index(): string
    {
        $status = $this->request->getGet('status') ?: 'all';
        $q = trim((string)$this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = max(1, min(100, (int) ($this->request->getGet('per_page') ?? 25)));

        try {
            $data = $this->orchestrator->getIssuesData($status, $q, $page, $perPage);

            return view('Dex\\dex/issues_list', array_merge($data->toArray(), [
                'title' => 'Issues',
                'dataUrl' => site_url(dex_route_prefix() . '/issues/data'),
                'detailBaseUrl' => site_url(dex_route_prefix() . '/issues'),
            ]));
        } catch (DexException $e) {
            return view('Dex\\dex/error', [
                'message' => 'Dex issues list is temporarily unavailable.',
            ]);
        }
    }

    public function data(): ResponseInterface
    {
        $status = $this->request->getGet('status') ?: 'all';
        $q = trim((string) $this->request->getGet('q'));
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = max(1, min(100, (int) ($this->request->getGet('per_page') ?? 25)));

        try {
            $data = $this->orchestrator->listIssues($status, $q, $page, $perPage);

            return $this->response->setJSON($data->toArray());
        } catch (DexException) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON([
                    'message' => 'Dex issues data is temporarily unavailable.',
                ]);
        }
    }

    public function dialog(int $id): ResponseInterface|string
    {
        $selectedId = (int) ($this->request->getGet('occ') ?? 0);

        try {
            $data = $this->orchestrator->showIssueDialogShell($id, $selectedId);

            return view('Dex\\dex/issues_dialog_shell', array_merge($data, [
                'dialogUrl' => site_url(dex_route_prefix() . '/issues/' . $id . '/dialog'),
                'resolveUrl' => site_url(dex_route_prefix() . '/issues/' . $id . '/resolve'),
                'ignoreUrl' => site_url(dex_route_prefix() . '/issues/' . $id . '/ignore'),
            ]));
        } catch (IssueNotFoundException) {
            return $this->response->setStatusCode(404)->setBody('Issue not found');
        } catch (DexException) {
            return $this->response->setStatusCode(503)->setBody('Issue dialog is temporarily unavailable.');
        }
    }

    public function dialogEvent(int $id): ResponseInterface|string
    {
        $selectedId = (int) ($this->request->getGet('occ') ?? 0);

        try {
            $data = $this->orchestrator->showIssueDialogEvent($id, $selectedId);

            return view('Dex\\dex/issues_dialog_event_content', $data);
        } catch (IssueNotFoundException) {
            return $this->response->setStatusCode(404)->setBody('Issue not found');
        } catch (DexException) {
            return $this->response->setStatusCode(503)->setBody('Issue event is temporarily unavailable.');
        }
    }

    public function dialogTab(int $id, string $tab): ResponseInterface|string
    {
        $selectedId = (int) ($this->request->getGet('occ') ?? 0);

        try {
            $data = $this->orchestrator->showIssueDialogTab($id, $selectedId, $tab);

            return view('Dex\\dex/issues_dialog_tab', $data);
        } catch (InvalidArgumentException $e) {
            return $this->response->setStatusCode(400)->setBody($e->getMessage());
        } catch (IssueNotFoundException) {
            return $this->response->setStatusCode(404)->setBody('Issue not found');
        } catch (DexException) {
            return $this->response->setStatusCode(503)->setBody('Issue dialog tab is temporarily unavailable.');
        }
    }

    public function resolve(int $id): ResponseInterface
    {
        try {
            $data = $this->orchestrator->resolveIssue($id);

            return $this->response->setJSON([
                'success' => true,
                'issue' => $data['issue'],
                'summary' => $data['summary'],
            ]);
        } catch (IssueNotFoundException) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Issue not found']);
        } catch (DexException) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON(['success' => false, 'message' => 'Issue resolve is temporarily unavailable.']);
        }
    }

    public function ignore(int $id): ResponseInterface
    {
        try {
            $data = $this->orchestrator->ignoreIssue($id);

            return $this->response->setJSON([
                'success' => true,
                'issue' => $data['issue'],
                'summary' => $data['summary'],
            ]);
        } catch (IssueNotFoundException) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['success' => false, 'message' => 'Issue not found']);
        } catch (DexException) {
            return $this->response
                ->setStatusCode(503)
                ->setJSON(['success' => false, 'message' => 'Issue ignore is temporarily unavailable.']);
        }
    }
}
