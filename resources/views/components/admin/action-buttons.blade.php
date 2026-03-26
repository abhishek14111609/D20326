@props([
    'viewRoute' => null,
    'editRoute' => null,
    'deleteRoute' => null,
    'viewTooltip' => 'View',
    'editTooltip' => 'Edit',
    'deleteTooltip' => 'Delete',
    'deleteConfirmMessage' => 'Are you sure you want to delete this item?',
    'showView' => true,
    'showEdit' => true,
    'showDelete' => true,
    'small' => true,
    'itemId' => null,
])

<div class="d-flex gap-2">
    @if($showView && $viewRoute)
        <a href="{{ $viewRoute }}" 
           class="btn btn-sm btn-outline-primary" 
           data-bs-toggle="tooltip" 
           title="{{ $viewTooltip }}">
            <i class='bx bx-show'></i>
            @if(!$small)<span class="d-none d-md-inline">{{ $viewTooltip }}</span>@endif
        </a>
    @endif

    @if($showEdit && $editRoute)
        <a href="{{ $editRoute }}" 
           class="btn btn-sm btn-outline-warning" 
           data-bs-toggle="tooltip" 
           title="{{ $editTooltip }}">
            <i class='bx bxs-edit-alt'></i>
            @if(!$small)<span class="d-none d-md-inline">{{ $editTooltip }}</span>@endif
        </a>
    @endif

    @if($showDelete && $deleteRoute)
        <form action="{{ $deleteRoute }}" 
              method="POST" 
              class="d-inline" 
              onsubmit="return confirm('{{ $deleteConfirmMessage }}')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="btn btn-sm btn-outline-danger" 
                    data-bs-toggle="tooltip" 
                    title="{{ $deleteTooltip }}">
                <i class='bx bxs-trash'></i>
                @if(!$small)<span class="d-none d-md-inline">{{ $deleteTooltip }}</span>@endif
            </button>
        </form>
    @endif
</div>
