
<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บริการซ่อมแอร์ - Mendcare Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="air.css">

</head>
<body>
    <div id="main-content">
        <header>
            <div class="header-container">
                <div class="nav-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </div>
                <a href="/" class="logo">
                    <i class="fas fa-wrench"></i>
                    Mendcare Service
                </a>
            </div>
        </header>

        <main>
        <section class="service-intro">
    <div class="container">
        <h1>บริการซ่อมแอร์</h1>
        <p>ให้บริการซ่อม ล้าง ติดตั้ง และบำรุงรักษาแอร์ทุกชนิด โดยทีมช่างผู้เชี่ยวชาญ</p>
    </div>
</section>

<section class="service-grid">
    <div class="container">
        <!-- Row 1 -->
        <div class="grid-row">
            <div class="grid-item" data-product="air1">
                <img src="image/air1.jpg" alt="ล้างแอร์บ้าน">
                <h3>ล้างแอร์บ้าน</h3>
                <p>เริ่มต้น 500 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air2">
                <img src="image/air2.jpg" alt="ล้างแอร์ออฟฟิศ">
                <h3>ล้างแอร์ออฟฟิศ</h3>
                <p>เริ่มต้น 650 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air3">
                <img src="image/air3.jpg" alt="ซ่อมแอร์ทั่วไป">
                <h3>ซ่อมแอร์ทั่วไป</h3>
                <p>เริ่มต้น 800 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air4">
                <img src="image/air4.jpg" alt="เติมน้ำยาแอร์">
                <h3>เติมน้ำยาแอร์</h3>
                <p>เริ่มต้น 700 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>
        </div>

        <!-- Row 2 -->
        <div class="grid-row">
            <div class="grid-item" data-product="air5">
                <img src="image/air5.jpg" alt="ติดตั้งแอร์ใหม่">
                <h3>ติดตั้งแอร์ใหม่</h3>
                <p>เริ่มต้น 2,500 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air6">
                <img src="image/air6.jpg" alt="ย้ายจุดติดตั้ง">
                <h3>ย้ายจุดติดตั้ง</h3>
                <p>เริ่มต้น 2,000 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air7">
                <img src="image/air7.jpg" alt="ล้างคอยล์เย็น">
                <h3>ล้างคอยล์เย็น</h3>
                <p>เริ่มต้น 600 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air8">
                <img src="image/air8.jpg" alt="ล้างคอยล์ร้อน">
                <h3>ล้างคอยล์ร้อน</h3>
                <p>เริ่มต้น 500 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>
        </div>

        <!-- Row 3 -->
        <div class="grid-row">
            <div class="grid-item" data-product="air9">
                <img src="image/air9.jpg" alt="เช็คระบบน้ำยา">
                <h3>เช็คระบบน้ำยา</h3>
                <p>เริ่มต้น 300 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air10">
                <img src="image/air10.jpg" alt="ซ่อมรีโมท">
                <h3>ซ่อมรีโมท</h3>
                <p>เริ่มต้น 250 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air11">
                <img src="image/air11.jpg" alt="เปลี่ยนคอมเพรสเซอร์">
                <h3>เปลี่ยนคอมเพรสเซอร์</h3>
                <p>เริ่มต้น 4,500 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>

            <div class="grid-item" data-product="air12">
                <img src="image/air12.jpg" alt="ตรวจเช็คทั่วไป">
                <h3>ตรวจเช็คทั่วไป</h3>
                <p>เริ่มต้น 200 บาท</p>
                <div class="button-group">
                    <button class="cart-btn">
                        <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                    </button>
                    <button class="book-btn">
                        <i class="fas fa-calendar-check"></i> จองเลย
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
        </main>

        <footer>
            <p>&copy; 2025 Mendcare Service. สงวนลิขสิทธิ์.</p>
        </footer>
    </div>

    <!-- Modal ต้องอยู่ตรงนี้ -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="product-details">
                <div class="modal-image-container">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="product-info">
                    <h2 id="modalTitle"></h2>
                    <p id="modalPrice" class="price"></p>
                    <div class="description">
                        <h3>รายละเอียดบริการ</h3>
                        <ul id="modalDescription"></ul>
                    </div>
                    <div class="button-group">
                        <button class="cart-btn">
                            <i class="fas fa-cart-plus"></i> เพิ่มลงตะกร้า
                        </button>
                        <button class="book-btn">
                            <i class="fas fa-calendar-check"></i> จองเลย
                        </button>
                    </div>
                </div>
            </div>
        </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Product Data
        const productData = {
    air1: {
        title: 'ล้างแอร์บ้าน',
        price: 'เริ่มต้น 500 บาท',
        description: [
            'ล้างทำความสะอาดแผงคอยล์เย็น',
            'ล้างแผงระบายความร้อน',
            'เช็คระบบน้ำยา',
            'ตรวจเช็คการทำงานทั่วไป',
            'รับประกันงาน 30 วัน'
        ]
    },
    air2: {
        title: 'ล้างแอร์ออฟฟิศ',
        price: 'เริ่มต้น 650 บาท',
        description: [
            'ทำความสะอาดระบบแอร์เชิงพาณิชย์',
            'บริการนอกเวลาได้',
            'รองรับแอร์หลายเครื่อง',
            'มีทีมงานมืออาชีพ',
            'รับประกันผลงาน 45 วัน'
        ]
    },
    air3: {
        title: 'ซ่อมแอร์ทั่วไป',
        price: 'เริ่มต้น 800 บาท',
        description: [
            'ตรวจเช็คอาการเสีย',
            'ซ่อมระบบไฟฟ้า',
            'แก้ไขระบบน้ำยา',
            'เปลี่ยนอะไหล่แท้',
            'รับประกันการซ่อม 60 วัน'
        ]
    },
    air4: {
        title: 'เติมน้ำยาแอร์',
        price: 'เริ่มต้น 700 บาท',
        description: [
            'ตรวจสอบการรั่วของน้ำยา',
            'เติมน้ำยาแอร์คุณภาพสูง',
            'ปรับแต่งระบบทำความเย็น',
            'ทดสอบประสิทธิภาพ',
            'รับประกัน 30 วัน'
        ]
    },
    air5: {
        title: 'ติดตั้งแอร์ใหม่',
        price: 'เริ่มต้น 2,500 บาท',
        description: [
            'ติดตั้งโดยช่างมืออาชีพ',
            'ใช้อุปกรณ์คุณภาพสูง',
            'รับประกันการติดตั้ง 1 ปี',
            'ฟรีค่าสำรวจหน้างาน',
            'บริการหลังการขาย'
        ]
    },
    air6: {
        title: 'ย้ายจุดติดตั้ง',
        price: 'เริ่มต้น 2,000 บาท',
        description: [
            'ถอดย้ายอย่างระมัดระวัง',
            'ติดตั้งจุดใหม่ได้มาตรฐาน',
            'ตรวจสอบระบบหลังติดตั้ง',
            'รับประกันงาน 90 วัน',
            'ให้คำปรึกษาฟรี'
        ]
    },
    air7: {
        title: 'ล้างคอยล์เย็น',
        price: 'เริ่มต้น 600 บาท',
        description: [
            'ทำความสะอาดคอยล์เย็นละเอียด',
            'ฆ่าเชื้อโรค',
            'ดูดฝุ่นและสิ่งสกปรก',
            'ปรับสมดุลระบบ',
            'รับประกันความสะอาด'
        ]
    },
    air8: {
        title: 'ล้างคอยล์ร้อน',
        price: 'เริ่มต้น 500 บาท',
        description: [
            'ล้างคอยล์ร้อนด้วยน้ำแรงดันสูง',
            'กำจัดคราบสกปรก',
            'เพิ่มประสิทธิภาพการระบายความร้อน',
            'ตรวจสอบพัดลมระบายความร้อน',
            'รับประกันผลงาน'
        ]
    },
    air9: {
        title: 'เช็คระบบน้ำยา',
        price: 'เริ่มต้น 300 บาท',
        description: [
            'ตรวจสอบแรงดันน้ำยา',
            'เช็คการรั่วซึม',
            'วัดอุณหภูมิระบบ',
            'ทดสอบการทำความเย็น',
            'แนะนำการบำรุงรักษา'
        ]
    },
    air10: {
        title: 'ซ่อมรีโมท',
        price: 'เริ่มต้น 250 บาท',
        description: [
            'ตรวจสอบอาการเสีย',
            'เปลี่ยนแบตเตอรี่',
            'ซ่อมแผงวงจร',
            'ทดสอบการใช้งาน',
            'รับประกัน 30 วัน'
        ]
    },
    air11: {
        title: 'เปลี่ยนคอมเพรสเซอร์',
        price: 'เริ่มต้น 4,500 บาท',
        description: [
            'เปลี่ยนคอมเพรสเซอร์ใหม่',
            'ใช้อะไหล่แท้ 100%',
            'ทดสอบระบบหลังเปลี่ยน',
            'รับประกัน 1 ปี',
            'ฟรีค่าแรงหากมีปัญหา'
        ]
    },
    air12: {
        title: 'ตรวจเช็คทั่วไป',
        price: 'เริ่มต้น 200 บาท',
        description: [
            'ตรวจสอบการทำงานทั่วไป',
            'เช็คระบบไฟฟ้า',
            'วัดอุณหภูมิ',
            'ตรวจสอบเสียงผิดปกติ',
            'ให้คำแนะนำการดูแลรักษา'
        ]
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("productModal");
    const modalImage = document.getElementById("modalImage");
    const modalTitle = document.getElementById("modalTitle");
    const modalPrice = document.getElementById("modalPrice");
    const modalDescription = document.getElementById("modalDescription");
    const closeModal = document.querySelector(".modal .close");

    const products = {
        air1: { title: "ล้างแอร์บ้าน", price: "เริ่มต้น 500 บาท", description: ["บริการล้างแอร์ในบ้าน", "ทีมงานมืออาชีพ"] },
        air2: { title: "ล้างแอร์ออฟฟิศ", price: "เริ่มต้น 650 บาท", description: ["เหมาะสำหรับออฟฟิศขนาดเล็ก", "ใช้น้ำยาฆ่าเชื้อคุณภาพสูง"] },
        // เพิ่มรายการอื่น ๆ ตามความต้องการ
    };

    // เปิด Modal
    document.querySelectorAll(".grid-item").forEach(item => {
    item.addEventListener("click", () => {
        const productKey = item.getAttribute("data-product");
        if (productData[productKey]) { // Change products to productData
            const product = productData[productKey];
            modalImage.src = item.querySelector("img").src;
            modalTitle.textContent = product.title;
            modalPrice.textContent = product.price;
            modalDescription.innerHTML = product.description
                .map(desc => `<li>${desc}</li>`)
                .join("");
            modal.style.display = "block";
        }
    });
});


    // ปิด Modal
    closeModal.addEventListener("click", () => {
        modal.style.display = "none";
    });

    // ปิด Modal เมื่อคลิกข้างนอก
    window.addEventListener("click", (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});

    </script>
</body>
</html>