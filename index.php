<?php include 'db.php'; ?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <title>التسجيل</title>
    <style>
    /* Changed from margin-left to margin-right */
    body {
        margin: 0;
        font-family: "Segoe UI", "Tahoma", "Arial", sans-serif;
        background-color: #f4f6f8;
        color: #333;
        direction: rtl;
    }

    .main {
        margin-right: 220px;
        /* Changed from margin-left to margin-right */
        padding: 40px 30px;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Heading */
    h1 {
        color: #0f766e;
        margin-bottom: 25px;
        font-size: 28px;
    }

    /* Form card */
    form {
        background: #ffffff;
        padding: 30px 35px;
        width: 100%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    form:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
    }

    /* Form groups */
    .nice-form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: #0f766e;
        font-size: 15px;
    }

    /* Inputs and selects */
    input,
    select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 15px;
        transition: border 0.2s, box-shadow 0.2s;
        text-align: right;
        /* Align text to right for Arabic */
    }

    input:focus,
    select:focus {
        border-color: #0f766e;
        box-shadow: 0 0 6px rgba(15, 118, 110, 0.25);
        outline: none;
    }

    /* Submit button */
    input[type="submit"] {
        background-color: #0f766e;
        color: #fff;
        border: none;
        padding: 14px 20px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s, transform 0.2s;
    }

    input[type="submit"]:hover {
        background-color: #115e59;
        transform: translateY(-2px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main {
            margin-right: 0;
            /* Changed from margin-left to margin-right */
            padding: 20px 15px;
        }

        form {
            width: 100%;
            padding: 20px;
            margin: 0;
        }

        h1 {
            font-size: 24px;
            text-align: center;
        }
    }
    </style>
</head>

<body>

    <?php include 'sidenav.php'; ?>

    <div class="main">
        <h1> محطوط التسجيل</h1>
        <form action="submit.php" method="post">
            <div class="nice-form-group">
                <label>الاسم</label>
                <input type="text" name="name" placeholder="ادخل الاسم الكامل" required />
            </div>
            <div class="nice-form-group">
                <label>رقم الهاتف</label>
                <input type="tel" name="phone" placeholder="+20XXXXXXXXXX" required />
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
                <input type="text" name="payment" placeholder="ادخل المبلغ المدفوع" required />
            </div>
            <input type="submit" value="إرسال">
        </form>
    </div>

</body>
<script>
document.getElementById('team').addEventListener('change', function() {
    const team = this.value;
    const gradeSelect = document.getElementById('grade');

    // Clear existing options
    gradeSelect.innerHTML = '<option value="" disabled selected>اختر الصف الدراسي</option>';

    let grades = [];

    switch (team) {
        case 'براعم':
            grades = [{
                    value: 'اولي ابتدائي',
                    text: 'أولي ابتدائي'
                },
                {
                    value: 'ثانيه ابتدائي',
                    text: 'ثانية ابتدائي'
                }
            ];
            break;
        case 'أشبال':
        case 'زهرات':
            grades = [{
                    value: 'ثالثة ابتدائي',
                    text: 'ثالثة ابتدائي'
                },
                {
                    value: 'رابعه ابتدائي',
                    text: 'رابعة ابتدائي'
                },
                {
                    value: 'خامسه ابتدائي',
                    text: 'خامسة ابتدائي'
                },
                {
                    value: 'سادسه ابتدائي',
                    text: 'سادسة ابتدائي'
                }
            ];
            break;
        case 'كشافة':
        case 'مرشدات':
            grades = [{
                    value: 'اولي اعدادي',
                    text: 'أولي إعدادي'
                },
                {
                    value: 'ثانيه اعدادي',
                    text: 'ثانية إعدادي'
                },
                {
                    value: 'ثالثة اعدادي',
                    text: 'ثالثة إعدادي'
                }
            ];
            break;
        case 'متقدم':
        case 'رائدات':

            grades = [{
                    value: 'اولي ثانوي',
                    text: 'أولي ثانوي'
                },
                {
                    value: 'ثانيه ثانوي',
                    text: 'ثانية ثانوي'
                },
                {
                    value: 'ثالثة ثانوي',
                    text: 'ثالثة ثانوي'
                }
            ];
            break;
            break;
        case 'متقدم':
        case 'رائدات':

            grades = [{
                    value: 'اولي ثانوي',
                    text: 'أولي ثانوي'
                },
                {
                    value: 'ثانيه ثانوي',
                    text: 'ثانية ثانوي'
                },
                {
                    value: 'ثالثة ثانوي',
                    text: 'ثالثة ثانوي'
                }
            ];
            break;
        case 'جوالة':
            grades = [{
                    value: 'جامعة',
                    text: 'جامعة'
                },
                {
                    value: 'خريج',
                    text: 'خريج'
                }
            ];
            break;
        case 'قادة':
            grades = [{
                    value: 'جامعة',
                    text: 'جامعة'
                },
                {
                    value: 'خريج',
                    text: 'خريج'
                }
            ];
            break;

    }

    // Add grades to select
    grades.forEach(grade => {
        const option = document.createElement('option');
        option.value = grade.value;
        option.textContent = grade.text;
        gradeSelect.appendChild(option);
    });

    document.getElementById('isCase').addEventListener('change', function() {
        const paymentField = document.getElementById('payment');
        if (this.checked) {
            paymentField.value = '0';
            paymentField.disabled = true;
        } else {
            paymentField.disabled = false;
        }
    });
});
</script>

</html>