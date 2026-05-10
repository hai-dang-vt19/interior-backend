{{-- Modal + polling đơn chờ xác nhận (khách đặt trên site). Icon nút mở modal nằm trong navbar. --}}
<div class="modal fade" id="adminPendingOrdersModal" tabindex="-1" aria-labelledby="adminPendingOrdersModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminPendingOrdersModalLabel">Đơn chờ xác nhận</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0">
                <p class="text-muted small px-3 pt-3 mb-0 d-none" id="adminPendingOrdersTruncNote"></p>
                <div class="list-group list-group-flush" id="adminPendingOrdersList"></div>
                <p class="text-muted small p-3 mb-0 d-none" id="adminPendingOrdersEmpty">Không có đơn chờ xác nhận.</p>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var url = @json(route('admin.order.pending-notifications'));
        var pollMs = 5 * 60 * 1000;

        function escapeHtml(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function updateBadge(count) {
            var badge = document.getElementById('adminPendingOrdersBadge');
            if (!badge) {
                return;
            }
            var n = parseInt(count, 10) || 0;
            if (n < 1) {
                badge.classList.add('d-none');
                badge.textContent = '0';
                return;
            }
            badge.classList.remove('d-none');
            badge.textContent = n > 99 ? '99+' : String(n);
        }

        function renderModal(data) {
            var orders = (data && data.orders) ? data.orders : [];
            var count = (data && data.count != null) ? parseInt(data.count, 10) : 0;
            var limit = (data && data.list_limit != null) ? parseInt(data.list_limit, 10) : 40;
            var listEl = document.getElementById('adminPendingOrdersList');
            var emptyEl = document.getElementById('adminPendingOrdersEmpty');
            var noteEl = document.getElementById('adminPendingOrdersTruncNote');
            if (!listEl || !emptyEl || !noteEl) {
                return;
            }
            listEl.innerHTML = '';
            if (!orders.length) {
                emptyEl.classList.remove('d-none');
                noteEl.classList.add('d-none');
                noteEl.textContent = '';
                return;
            }
            emptyEl.classList.add('d-none');
            if (count > orders.length) {
                noteEl.classList.remove('d-none');
                noteEl.textContent =
                    'Tổng ' + count + ' đơn chờ; hiển thị ' + orders.length + ' đơn mới nhất (giới hạn ' + limit + ').';
            } else {
                noteEl.classList.add('d-none');
                noteEl.textContent = '';
            }
            orders.forEach(function(row) {
                var code = row.order_code ? escapeHtml(row.order_code) : '#' + row.id;
                var name = escapeHtml(row.customer_name || '—');
                var total = escapeHtml(row.total_display || '');
                var created = escapeHtml(row.created_at || '');
                var href = row.url || '#';
                var a = document.createElement('a');
                a.href = href;
                a.className = 'list-group-item list-group-item-action admin-pending-order-item';
                a.innerHTML =
                    '<div class="d-flex w-100 justify-content-between align-items-start gap-2">' +
                    '<div><strong>' + code + '</strong>' +
                    '<div class="small text-muted">' + name + '</div></div>' +
                    '<div class="text-end small"><div>' + total + '</div>' +
                    '<div class="text-muted">' + created + '</div></div></div>';
                listEl.appendChild(a);
            });
        }

        function fetchPending() {
            fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function(r) {
                    return r.json();
                })
                .then(function(res) {
                    if (!res || !res.success || !res.data) {
                        return;
                    }
                    updateBadge(res.data.count);
                    renderModal(res.data);
                })
                .catch(function() {});
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('adminPendingOrdersBtn')) {
                return;
            }
            fetchPending();
            setInterval(fetchPending, pollMs);
            var modalEl = document.getElementById('adminPendingOrdersModal');
            if (modalEl) {
                modalEl.addEventListener('show.bs.modal', function() {
                    fetchPending();
                });
            }
        });
    })();
</script>
