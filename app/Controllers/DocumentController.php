<?php

namespace App\Controllers;

use Slim\Http\Response;
use Slim\Http\Request;
use App\Validators\DocumentValidator;
use App\Models\User;
use App\Models\System;
use App\Models\Process;
use App\Models\DocumentType;
use App\Models\Document;
use App\Support\Datatable\Datatable;

class DocumentController extends Controller
{
    /**
     * Index
     */
    public function index(Request $request, Response $response): Response
    {
        $this->authorize('documents.show');

        return $this->render($response, 'documents.index');
    }

    /**
     * Show
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        $this->authorize('documents.show');

        $document = Document::findOrFail($args['document']);

        return $this->render($response, 'documents.show', compact('document'));
    }

    /**
     * Create
     */
    public function create(Request $request, Response $response): Response
    {
        $this->authorize('documents.create');

        return $this->createOrEdit($request, $response);
    }

    /**
     * Store
     */
    public function store(Request $request, Response $response): Response
    {
        $this->authorize('documents.create');

        return $this->storeOrUpdate($request, $response);
    }

    /**
     * Edit
     */
    public function edit(Request $request, Response $response, array $args): Response
    {
        $document = Document::findOrFail($args['document']);

        $this->authorize('edit', $document);

        return $this->createOrEdit($request, $response, $document);
    }

    /**
     * Update
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $document = Document::findOrFail($args['document']);

        $this->authorize('edit', $document);

        return $this->storeOrUpdate($request, $response, $document);
    }

    /**
     * Destroy
     */
    public function destroy(Request $request, Response $response, array $args): Response
    {
        $document = Document::findOrFail($args['document']);

        $this->authorize('delete', $document);

        try {
            $document->delete();
        } catch (\Throwable $t) {
            $this->flash->addMessage('error', MSG_ERROR_DELETE);

            return $this->redirect(
                $request,
                $response,
                'documents.show',
                [
                    'document' => $document->id,
                ]
            );
        }
        
        $this->flash->addMessage('success', sprintf(MSG_DELETED, $document->name));
        return $this->redirect($request, $response, 'documents.index');
    }

    /**
     * Datatable
     */
    public function datatable(Request $request, Response $response): Response
    {
        $this->authorize('documents.show');

        /** @var \Slim\Router */
        $router = $this->get('router');

        /** @var \Illuminate\Database\Query\Builder */
        $query = Document::select([
            'documents.id',
            'documents.name',
        ])->getQuery();

        return (new Datatable($query, $request, $response)) 
            ->addColumn('show_url', function ($row) use ($router) {
                return $router->pathFor('documents.show', ['document' => $row->id]);
            })
            ->response();
    }

    /**
     * Create or edit
     */
    private function createOrEdit(Request $request, Response $response, Document $document = null): Response
    {
        $systems = System::orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $processes = Process::active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $users = User::active()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();

        $documentsTypes = DocumentType::orderBy('name')
            ->get();

        return $this->render(
            $response,
            'documents.'.($document? 'edit': 'create'),
            [
                'document'        => $document,
                'systems'         => $systems,
                'processes'       => $processes,
                'users'           => $users,
                'documents_types' => $documentsTypes,
            ]
        );
    }

    /**
     * Store or update
     */
    private function storeOrUpdate(Request $request, Response $response, Document $document = null): Response
    {
        $attributes = DocumentValidator::validate($request);

        if ($document) {
            $document->fill($attributes);
        } else {
            $document = new Document($attributes);
        }

        $document->save();

        return $this->redirect($request, $response, 'documents.show', ['document' => $document->id]);
    }
}
