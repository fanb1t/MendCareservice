const AuthManager = {
    init() {
        this.bindEvents();
    },

    bindEvents() {
        window.openLoginPopup = () => this.openLoginPopup();
        window.closeLoginPopup = () => this.closeLoginPopup();
        window.showLoginForm = () => this.showLoginForm();
        window.showRegisterForm = () => this.showRegisterForm();
        window.handleLogin = (event) => this.handleLogin(event);
        window.handleRegister = (event) => this.handleRegister(event);
        window.handleLogout = () => this.handleLogout();
    },

    openLoginPopup() {
        document.getElementById("loginPopup").style.display = "flex";
    },

    closeLoginPopup() {
        document.getElementById("loginPopup").style.display = "none";
    },

    showLoginForm() {
        document.getElementById("login-form").classList.add("active");
        document.getElementById("register-form").classList.remove("active");
    },

    showRegisterForm() {
        document.getElementById("register-form").classList.add("active");
        document.getElementById("login-form").classList.remove("active");
    },

    handleLogin(event) {
        event.preventDefault();
        
        const email = document.querySelector('#login-form input[name="email"]').value;
        const password = document.querySelector('#login-form input[name="password"]').value;
        
        const formData = new FormData();
        formData.append('action', 'login');
        formData.append('email', email);
        formData.append('password', password);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                this.closeLoginPopup();
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    },

    handleRegister(event) {
        event.preventDefault();
        const formData = new FormData(event.target);
        formData.append('action', 'register');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                this.closeLoginPopup();
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        });
    },

    handleLogout() {
        const formData = new FormData();
        formData.append('action', 'logout');

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'ออกจากระบบสำเร็จ!',
                    text: 'ขอบคุณที่ใช้บริการ',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    location.reload();
                    setTimeout(() => {
                        window.location.href = 'ind.php';
                    }, 100);
                });
            }
        });
    }
};

const CartManager = {
    init() {
        window.addToCart = (subServiceId) => this.addToCart(subServiceId);
    },

    addToCart(subServiceId) {
        if (!isUserLoggedIn) {
            Swal.fire({
                title: 'กรุณาเข้าสู่ระบบ',
                text: 'คุณต้องเข้าสู่ระบบก่อนเพิ่มบริการลงตะกร้า',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'เข้าสู่ระบบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    AuthManager.openLoginPopup();
                }
            });
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('sub_service_id', subServiceId);

        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    title: 'เพิ่มลงตะกร้าสำเร็จ!',
                    icon: 'success',
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    title: 'แจ้งเตือน',
                    text: data.message,
                    icon: 'info'
                });
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    AuthManager.init();
    CartManager.init();
});
