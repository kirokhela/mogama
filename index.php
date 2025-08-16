<?php
$pageContent = '

<style>
    /* Custom styles for the registration form */
    .main {
        max-width: 600px; 
        margin: 40px auto;
        padding: 25px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
    }

    .main h1 {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 20px;
        font-size: 28px;
        font-weight: 700;
    }

    .nice-form-group {
        margin-bottom: 18px;
    }

    .nice-form-group label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #34495e;
        font-size: 15px;
    }

    .nice-form-group input[type="text"],
    .nice-form-group input[type="tel"],
    .nice-form-group select {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e1e8ed;
        border-radius: 8px;
        font-size: 15px;
        color: #333;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .nice-form-group input[type="text"]:focus,
    .nice-form-group input[type="tel"]:focus,
    .nice-form-group select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        outline: none;
    }

    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #3498db;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease, transform 0.2s ease;
        margin-top: 15px;
    }

    input[type="submit"]:hover {
        background-color: #2980b9;
        transform: translateY(-2px);
    }

    /* Logo above the title */
    .form-header {
        text-align: center;
        margin-bottom: 15px;
    }
    .form-header img {
        max-width: 60px;
        height: auto;
        display: block;
        margin: 0 auto 10px auto;
    }

    @media (max-width: 600px) {
        .main {
            margin: 20px auto;
            padding: 20px;
        }

        .main h1 {
            font-size: 24px;
        }
    }
</style>

<div class="main">
    <form action="submit.php" method="post">
   
        <div class="form-header">
            <img src="shamandora.png" alt="Logo"> 
            <h1>التسجيل</h1>
        </div>

        <div class="nice-form-group">
            <label>الاسم</label>
            <input type="text" name="name" placeholder="ادخل الاسم الكامل" required />
        </div>
        <div class="nice-form-group">
            <label>رقم الهاتف</label>
            <input type="tel" name="phone" placeholder="0XXXXXXXXXX" required />
        </div>
        <div class="nice-form-group">
            <label>الفريق</label>
            <select name="team" id="team" required>
                <option value="" disabled selected>اختر الفريق</option>
                <option value="براعم">براعم</option>
                <option value="أشبال">أشبال</option>
                <option value="زهرات">زهرات</option>
                <option value="كشافة">كشافة</option>
                <option value="مرشدات">مرشدات</option>
                <option value="متقدم">متقدم</option>
                <option value="رائدات">رائدات</option>
                <option value="جوالة">جوالة</option>
            </select>
        </div>
        <div class="nice-form-group">
            <label>الصف الدراسي</label>
            <select name="grade" id="grade" required>
                <option value="" disabled selected>اختر الصف الدراسي</option>
            </select>
        </div>
        <div class="nice-form-group" style="display: flex; align-items: center; gap: 6px;">
            <label for="isCase" style="margin: 0; cursor: pointer; font-weight: bold;">حالة خاصة (IsCase)</label>
            <input type="checkbox" id="isCase" name="isCase" value="1"
                style="width: 18px; height: 18px; cursor: pointer;">
        </div>
        <div class="nice-form-group">
            <label>المبلغ المدفوع</label>
            <input type="text" id="payment" name="payment" placeholder="ادخل المبلغ المدفوع" required />
        </div>
        <input type="submit" value="إرسال">
    </form>
</div>

<script>
document.getElementById("team").addEventListener("change", function() {
    const team = this.value;
    const gradeSelect = document.getElementById("grade");

    gradeSelect.innerHTML = \'<option value="" disabled selected>اختر الصف الدراسي</option>\';

    let grades = [];

    switch (team) {
        case "براعم":
            grades = [
                {value: "اولي ابتدائي", text: "أولي ابتدائي"},
                {value: "ثانيه ابتدائي", text: "ثانية ابتدائي"}
            ];
            break;
        case "أشبال":
        case "زهرات":
            grades = [
                {value: "ثالثة ابتدائي", text: "ثالثة ابتدائي"},
                {value: "رابعه ابتدائي", text: "رابعة ابتدائي"},
                {value: "خامسه ابتدائي", text: "خامسة ابتدائي"},
                {value: "سادسه ابتدائي", text: "سادسة ابتدائي"}
            ];
            break;
        case "كشافة":
        case "مرشدات":
            grades = [
                {value: "اولي اعدادي", text: "أولي إعدادي"},
                {value: "ثانيه اعدادي", text: "ثانية إعدادي"},
                {value: "ثالثة اعدادي", text: "ثالثة إعدادي"}
            ];
            break;
        case "متقدم":
        case "رائدات":
            grades = [
                {value: "اولي ثانوي", text: "أولي ثانوي"},
                {value: "ثانيه ثانوي", text: "ثانية ثانوي"},
                {value: "ثالثة ثانوي", text: "ثالثة ثانوي"}
            ];
            break;
        case "جوالة":
        case "قادة":
            grades = [
                {value: "جامعة", text: "جامعة"},
                {value: "خريج", text: "خريج"}
            ];
            break;
    }

    grades.forEach(grade => {
        const option = document.createElement("option");
        option.value = grade.value;
        option.textContent = grade.text;
        gradeSelect.appendChild(option);
    });
});

document.getElementById("isCase").addEventListener("change", function() {
    const paymentField = document.getElementById("payment");
    if (this.checked) {

    } else {
        paymentField.disabled = false;
    }
});
</script>
';
include "layout.php";