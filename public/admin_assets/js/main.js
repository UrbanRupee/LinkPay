function redirect(url) {
    window.location.href = url;
}
function formasync(id) {
    $("#" + id).on('submit', function (e) {
        e.preventDefault();
    });
}
function copy(text) {
    navigator.clipboard.writeText(text).then(function () {
        message({
            'title': 'Success!',
            'message': 'Text copy successfully!',
            'type': 1
        });
    }, function (err) {
        message({
            'title': 'Oops!',
            'message': 'Your text is not copy, please try again!',
            'type': 0
        });
    });
}
function message(data, position = 'top-end') {
    const Toast = Swal.mixin({
        toast: true,
        position: position,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
    if (data.type == 1 || data.status == 1) {
        Toast.fire({
            icon: 'success',
            title: data.title
        });
    } else if (data.type == 0 || data.status == 0) {
        Toast.fire({
            icon: 'error',
            title: data.title
        });
    }
}
function apex(method, url, data, form, success = null, error = null, reset = false, requestData = true) {
    if (requestData) {
        $.ajax({
            type: method,
            url: url,
            data: data,
            contentType: false,
            cache: false,
            processData: false,
            dataType: "json",
            beforeSend: function () {
                $(form).find('button[type=submit]').html(`<img src="https://media.tenor.com/On7kvXhzml4AAAAj/loading-gif.gif" alt="loading" style="width:30px;height30px;">`);
                $(form).find('button[type=submit]').attr('disable', true);
            },
            success: function (response) {
                if (response && response != null && response != '') {
                    if (response.status == 1) {
                        message(response);
                        if (success) {
                            setTimeout(() => {
                                window.location.href = success;
                            }, 1000);
                        }
                        $(form).find('button[type=submit]').html('success');
                    } else if (response.status == 0) {
                        $(form).find('button[type=submit]').attr('disable', false);
                        message(response);
                        if (error) {
                            setTimeout(() => {
                                window.location.href = error;
                            }, 1000);
                        }
                        $(form).find('button[type=submit]').html('Retry');
                    } else {
                        $(form).find('button[type=submit]').attr('disable', false);
                        message({
                            'title': 'Oops,Server Error, Please retry',
                            'type': 0
                        });
                        $(form).find('button[type=submit]').html('Retry');
                    }
                    if (reset) {
                        $(form).trigger("reset");
                    }
                } else {
                    $(form).find('button[type=submit]').attr('disable', false);
                    message({
                        'title': 'Oops,Server Error, Please retry',
                        'type': 0
                    });
                    $(form).find('button[type=submit]').html('Retry');
                }
            },
            error: function (e) {
                console.log(e);
                $(form).find('button[type=submit]').attr('disable', false);
                $(form).find('button[type=submit]').html('Retry');
                if (e.status == 422) {
                    message({
                        'title': 'Oops, ' + e.responseJSON.message,
                        'type': 0
                    });
                } else {
                    $(form).find('button[type=submit]').attr('disable', false);
                    message(response);
                    console.log(e);
                    $(form).find('button[type=submit]').html('Retry');
                }
            }
        });
    } else {
        $.ajax({
            type: method,
            url: url,
            dataType: "json",
            success: function (response) {
                if (response.status == 1) {
                    message(response);
                    if (success) {
                        setTimeout(() => {
                            window.location.href = success;
                        }, 1500);
                    }
                } else {
                    swal(response.title, response.message, response.type);
                    if (error) {
                        setTimeout(() => {
                            window.location.href = error;
                        }, 1500);
                    }
                }
                if (reset) {
                    $(form).trigger("reset");
                }
            },
            error: function (e) { }
        });
    }
}

function addtocart(id) {
    $.ajax({
        type: "GET",
        url: "/api/addtocart",
        data: {
            "product_id" : id
        },
        dataType: "json",
        success: function (response) {
            if (response.status == 1) {
                message(response);
            } else {
                message(response);
            }
        },
        error: function (e) { }
    });
}
function addwishlist(id) {
    $.ajax({
        type: "GET",
        url: "/api/addtowishlist",
        data: {
            "product_id" : id
        },
        dataType: "json",
        success: function (response) {
            if (response.status == 1) {
                message(response);
            } else {
                message(response);
            }
        },
        error: function (e) { }
    });
}

function deletecartproduct(id) {
    $.ajax({
        type: "GET",
        url: "/api/deletecartproduct",
        data: {
            "id" : id
        },
        dataType: "json",
        success: function (response) {
            if (response.status == 1) {
                message(response);
                $("#cartproduct"+id).remove();
            } else {
                message(response);
            }
        },
        error: function (e) { }
    });
}
function deletewishlistproduct(id) {
    $.ajax({
        type: "GET",
        url: "/api/deletewishlistproduct",
        data: {
            "id" : id
        },
        dataType: "json",
        success: function (response) {
            if (response.status == 1) {
                message(response);
                $("#row"+id).remove();
            } else {
                message(response);
            }
        },
        error: function (e) { }
    });
}