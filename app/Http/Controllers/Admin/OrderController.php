<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\OrderEditRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\OrderReturnRequestStore;
use App\Http\Requests\OrderReturnRequestUpdate;
use App\Http\Requests\OrderShippingRequest;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends BaseController
{
    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orderService->getOrders($request->all());
        $customers = $this->orderService->getCustomers();
        $products = $this->orderService->getProducts();

        return view('order.index', compact('orders', 'customers', 'products'));
    }

    public function store(OrderRequest $request)
    {
        $this->orderService->createOrder($request->validated());

        return redirect()->back()->with('dataSuccess', 'Tạo mới đơn hàng thành công');
    }

    public function edit(int $id)
    {
        return $this->orderService->getOrderByID($id);
    }

    public function update(int $id, OrderEditRequest $request)
    {
        $this->orderService->updateOrderByID($id, $request->validated());

        return redirect()->back()->with('dataSuccess', 'Cập nhật đơn hàng thành công');
    }

    public function destroy(int $id)
    {
        $this->orderService->destroy($id);

        return redirect()->back()->with('dataSuccess', 'Hủy đơn hàng thành công');
    }

    public function restore(int $id)
    {
        $this->orderService->restore($id);

        return redirect()->back()->with('dataSuccess', 'Khôi phục đơn hàng thành công');
    }

    public function forceDelete(int $id)
    {
        $this->orderService->forceDelete($id);

        return redirect()->back()->with('dataSuccess', 'Xóa vĩnh viễn đơn hàng thành công');
    }

    public function show(int $id)
    {
        $order = $this->orderService->getOrderDetail($id);

        return view('order.show', compact('order'));
    }

    public function storeReturnRequest(int $id, OrderReturnRequestStore $request)
    {
        $this->orderService->addReturnRequest($id, $request->validated(), Auth::id());

        return redirect()->back()->with('dataSuccess', 'Đã tạo yêu cầu đổi/trả hàng');
    }

    public function updateReturnRequestStatus(int $id, int $returnId, OrderReturnRequestUpdate $request)
    {
        $this->orderService->updateReturnRequestStatus($id, $returnId, $request->validated()['status'], Auth::id());

        return redirect()->back()->with('dataSuccess', 'Đã cập nhật trạng thái yêu cầu đổi/trả');
    }

    public function invoice(int $id): Response
    {
        $order = $this->orderService->getOrderDetail($id);
        $pdf = Pdf::loadView('order.invoice', compact('order'));
        $fileName = 'invoice-order-' . $order->id . '.pdf';

        return $pdf->download($fileName);
    }

    public function updateShipping(int $id, OrderShippingRequest $request)
    {
        $this->orderService->updateShipping($id, $request->validated(), Auth::id());

        return redirect()->back()->with('dataSuccess', 'Cập nhật giao hàng thành công');
    }
}
