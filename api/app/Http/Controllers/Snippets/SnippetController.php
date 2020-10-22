<?php

namespace App\Http\Controllers\Snippets;

use App\Http\Controllers\Controller;
use App\Models\Snippet;
use App\Transformers\Snippets\SnippetTransformer;
use Illuminate\Http\Request;

class SnippetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:api'])
            ->only('store', 'update');
    }

    public function index(Request $request){
        return fractal()
            ->collection(
                Snippet::take($request->get('limit', 10))->latest()->public()->get()
            )
            ->parseIncludes(['author'])
            ->transformWith(new SnippetTransformer())
            ->toArray();
    }

    public function show(Snippet $snippet)
    {
        $this->authorize('show', $snippet);

        return fractal()
            ->item($snippet)
            ->transformWith(new SnippetTransformer())
            ->parseIncludes(['steps', 'author', 'user'])
            ->toArray();
    }

    public function store(Request $request)
    {
        $snippet = $request->user()->snippets()->create();

        return fractal()
            ->item($snippet)
            ->transformWith(new SnippetTransformer())
            ->toArray();
    }

    public function update(Snippet $snippet, Request $request)
    {
        $this->authorize('update', $snippet);

        $this->validate($request, [
            'title' => 'nullable',
            'is_public' => 'nullable|boolean'
        ]);

        $snippet->update($request->only('title', 'is_public'));
    }

    public function destroy(Snippet $snippet)
    {
        $this->authorize('delete', $snippet);

        $snippet->delete();
    }
}
