const orders = document.getElementById('orders');

if (orders) {
    orders.addEventListener('click', (e) => {
        if (e.target.className === 'btn btn-danger delete-order') {
            if (confirm("Are you sure?")) {
                const id = e.target.getAttribute('data-id')

                fetch(`/order/delete/${id}`, {
                    method: 'DELETE'
                }).then(res => window.location.reload())
            }
        }
    });
}

