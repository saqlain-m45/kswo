$(function () {
    $('#menuToggle').on('click', function () {
        $('#mainNav').toggleClass('show');
    });

    function passwordStrength(value) {
        let score = 0;
        if (value.length >= 8) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[a-z]/.test(value)) score++;
        if (/\d/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;

        if (score <= 2) return { text: 'Weak', color: '#c62828' };
        if (score <= 4) return { text: 'Medium', color: '#d97706' };
        return { text: 'Strong', color: '#166534' };
    }

    $('#password').on('input', function () {
        const result = passwordStrength($(this).val());
        $('#passwordStrength').text('Strength: ' + result.text).css('color', result.color);
    });

    $('#registerForm').on('submit', function (e) {
        let valid = true;
        $('.field-error').text('');

        const cnic = $('#cnic').val().trim();
        const cnicRegex = /^\d{5}-\d{7}-\d{1}$/;
        if (!cnicRegex.test(cnic)) {
            $('#cnicError').text('CNIC must be in format 12345-1234567-1');
            valid = false;
        }

        const pass = $('#password').val();
        const confirmPass = $('#confirmPassword').val();
        if (pass.length < 8) {
            $('#passwordError').text('Password must be at least 8 characters.');
            valid = false;
        }
        if (pass !== confirmPass) {
            $('#confirmPasswordError').text('Passwords do not match.');
            valid = false;
        }

        if (!valid) e.preventDefault();
    });

    $('.step-next').on('click', function () {
        const current = Number($(this).data('current'));
        const next = current + 1;
        $('.step').removeClass('active');
        $('.step[data-step=' + next + ']').addClass('active');
        $('.step-pane').removeClass('active');
        $('#stepPane' + next).addClass('active');
    });

    $('.step-prev').on('click', function () {
        const current = Number($(this).data('current'));
        const prev = current - 1;
        $('.step').removeClass('active');
        $('.step[data-step=' + prev + ']').addClass('active');
        $('.step-pane').removeClass('active');
        $('#stepPane' + prev).addClass('active');
    });

    $('#confirmDonation').on('click', function () {
        const amount = $('#donationAmount').val();
        const method = $('input[name=payment_method]:checked').val();
        if (!amount || !method) {
            alert('Please choose amount and payment method before confirming.');
            return;
        }

        $('#receiptAmount').text('PKR ' + amount);
        $('#receiptMethod').text(method);
        $('#receiptStatus').text('Paid');
        $('.step-next[data-current=2]').trigger('click');
    });

    function toggleMethodAccounts(containerSelector, method) {
        const wrap = $(containerSelector);
        if (!wrap.length) return;

        if (!method) {
            wrap.hide();
            wrap.find('.method-accounts').hide();
            return;
        }

        wrap.show();
        wrap.find('.method-accounts').hide();
        wrap.find('.method-accounts[data-method="' + method + '"]').show();
    }

    $('input[name=payment_method]').on('change', function () {
        const selected = $(this).val();
        toggleMethodAccounts('#methodAccountsWrap', selected);
        toggleMethodAccounts('#methodAccountsWrapPublic', selected);
    });

    const selectedMethod = $('input[name=payment_method]:checked').val();
    toggleMethodAccounts('#methodAccountsWrap', selectedMethod);
    toggleMethodAccounts('#methodAccountsWrapPublic', selectedMethod);

    $(document).on('click', '.copy-account-btn', async function () {
        const button = $(this);
        const number = button.data('copy');
        if (!number) return;

        const defaultText = button.data('default') || 'Copy Number';

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(String(number));
            } else {
                const tempInput = document.createElement('input');
                tempInput.value = String(number);
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand('copy');
                document.body.removeChild(tempInput);
            }
            button.text('Copied');
            setTimeout(function () {
                button.text(defaultText);
            }, 1200);
        } catch (error) {
            button.text('Copy Failed');
            setTimeout(function () {
                button.text(defaultText);
            }, 1200);
        }
    });

    $('#transparencySearch, #monthFilter, #yearFilter').on('input change', function () {
        const keyword = ($('#transparencySearch').val() || '').toLowerCase();
        const month = $('#monthFilter').val();
        const year = $('#yearFilter').val();

        $('#transparencyTable tbody tr').each(function () {
            const row = $(this);
            const donor = row.find('td:eq(0)').text().toLowerCase();
            const rowMonth = row.data('month');
            const rowYear = String(row.data('year'));

            const nameOk = donor.includes(keyword);
            const monthOk = !month || rowMonth === month;
            const yearOk = !year || rowYear === year;

            row.toggle(nameOk && monthOk && yearOk);
        });
    });

    if ($('#donationChart').length) {
        const ctx = document.getElementById('donationChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Monthly Donations (PKR)',
                    data: [45000, 52000, 48000, 61000, 57000, 69000],
                    backgroundColor: '#0a66c2'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    $('.member-detail-btn').on('click', function () {
        const name = $(this).data('name');
        const cnic = $(this).data('cnic');
        const phone = $(this).data('phone');
        const status = $(this).data('status');

        $('#modalName').text(name);
        $('#modalCnic').text(cnic);
        $('#modalPhone').text(phone);
        $('#modalStatus').text(status);
        $('#memberModal').css('display', 'flex');
    });

    $('.close-modal').on('click', function () {
        $('#memberModal').hide();
    });

    $('#exportCsv').on('click', function () {
        const rows = [['Donor', 'Type', 'Amount', 'Date', 'Status', 'Method', 'Receipt']];
        $('#adminDonationTable tbody tr').each(function () {
            const rowData = [];
            $(this).find('td').each(function () {
                rowData.push($(this).text().trim());
            });
            if (rowData.length) rows.push(rowData);
        });

        const csv = rows.map(r => r.join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'donations-export.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });
});
