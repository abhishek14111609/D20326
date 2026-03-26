<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;

class ActionButtons extends Component
{
    public $viewRoute;
    public $editRoute;
    public $deleteRoute;
    public $viewTooltip;
    public $editTooltip;
    public $deleteTooltip;
    public $deleteConfirmMessage;
    public $showView;
    public $showEdit;
    public $showDelete;
    public $small;
    public $itemId;

    /**
     * Create a new component instance.
     *
     * @param string|null $viewRoute
     * @param string|null $editRoute
     * @param string|null $deleteRoute
     * @param string $viewTooltip
     * @param string $editTooltip
     * @param string $deleteTooltip
     * @param string $deleteConfirmMessage
     * @param bool $showView
     * @param bool $showEdit
     * @param bool $showDelete
     * @param bool $small
     * @param int|string|null $itemId
     */
    public function __construct(
        $viewRoute = null,
        $editRoute = null,
        $deleteRoute = null,
        $viewTooltip = 'View',
        $editTooltip = 'Edit',
        $deleteTooltip = 'Delete',
        $deleteConfirmMessage = 'Are you sure you want to delete this item?',
        $showView = true,
        $showEdit = true,
        $showDelete = true,
        $small = true,
        $itemId = null
    ) {
        $this->viewRoute = $viewRoute;
        $this->editRoute = $editRoute;
        $this->deleteRoute = $deleteRoute;
        $this->viewTooltip = $viewTooltip;
        $this->editTooltip = $editTooltip;
        $this->deleteTooltip = $deleteTooltip;
        $this->deleteConfirmMessage = $deleteConfirmMessage;
        $this->showView = $showView;
        $this->showEdit = $showEdit;
        $this->showDelete = $showDelete;
        $this->small = $small;
        $this->itemId = $itemId;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.admin.action-buttons');
    }
}
