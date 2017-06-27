<?php

namespace App\Blog\Controller\Admin;

use App\Blog\Table\CategoriesTable;
use Core\Controller;
use Core\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Request;

class CategoriesController extends Controller
{
    public function index(CategoriesTable $categoriesTable, Request $request): string
    {
        $page = $request->getParam('page', 1);
        $categories = $categoriesTable->findPaginated(10, $page);

        return $this->render('@blog/admin/categories/index', compact('categories'));
    }

    public function create(ServerRequestInterface $request, CategoriesTable $categoriesTable)
    {
        if ($request->getMethod() === 'POST') {
            $category = $this->getParams($request);
            $errors = $this->validates($request, $categoriesTable);

            if (empty($errors)) {
                $categoriesTable->create($category);
                $this->flash('success', 'La catégorie a bien été créée');

                return $this->redirect('blog.admin.category.index');
            }
        }

        return $this->render('@blog/admin/categories/create', compact('category', 'errors'));
    }

    public function edit(int $id, ServerRequestInterface $request, CategoriesTable $categoriesTable)
    {
        $category = $categoriesTable->findOrFail($id);

        if ($request->getMethod() === 'PUT') {
            $category = $this->getParams($request);
            $errors = $this->validates($request, $categoriesTable, $id);
            if (empty($errors)) {
                $categoriesTable->update($id, $category);
                $this->flash('success', 'La catégorie a bien été modifiée');

                return $this->redirect('blog.admin.category.index');
            }
        }

        return $this->render('@blog/admin/categories/edit', compact('category', 'errors'));
    }

    public function destroy(int $id, CategoriesTable $categoriesTable): ResponseInterface
    {
        $categoriesTable->delete($id);
        $this->flash('success', 'La catégorie a bien été supprimée');

        return $this->redirect('blog.admin.category.index');
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return array
     */
    private function getParams(ServerRequestInterface $request): array
    {
        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug'], true);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Valide les données.
     *
     * @param ServerRequestInterface $request
     * @param CategoriesTable        $categoriesTable
     * @param int|null               $categoryId
     *
     * @return array|bool
     */
    private function validates(ServerRequestInterface $request, CategoriesTable $categoriesTable, ?int $categoryId = null): array
    {
        $params = $request->getParsedBody();
        $validator = (new Validator($params))
            ->required('name', 'slug')
            ->slug('slug')
            ->unique('slug', $categoriesTable, $categoryId)
            ->minLength('name', 4);

        return $validator->getErrors();
    }
}