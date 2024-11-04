$(document).ready(function () {
    // Initial data (replace with your own)
    var users = [
        { id: 1, name: "John Doe", email: "john@example.com", role: "Admin" },
        { id: 2, name: "Jane Smith", email: "jane@example.com", role: "User" },
        // Add more user data here
    ];

    // Initialize variables
    var currentPage = 1;
    var itemsPerPage = 5;
    var totalItems = users.length;

    // Display initial user data
    displayUsers();

    // Search input event handler
    $("#searchInput").keyup(function () {
        displayUsers();
    });

    // Function to display users based on search and pagination
    function displayUsers() {
        var searchInput = $("#searchInput").val().toLowerCase();
        var filteredUsers = users.filter(function (user) {
            return user.name.toLowerCase().includes(searchInput) || user.email.toLowerCase().includes(searchInput);
        });

        var startIndex = (currentPage - 1) * itemsPerPage;
        var endIndex = startIndex + itemsPerPage;
        var paginatedUsers = filteredUsers.slice(startIndex, endIndex);

        var html = "";
        paginatedUsers.forEach(function (user) {
            html += "<tr>";
            html += "<td>" + user.id + "</td>";
            html += "<td>" + user.name + "</td>";
            html += "<td>" + user.email + "</td>";
            html += "<td>" + user.role + "</td>";
            html += "</tr>";
        });

        $("#userTableBody").html(html);

        // Update pagination
        updatePagination(filteredUsers.length);
    }

    // Function to update pagination
    function updatePagination(totalItems) {
        var totalPages = Math.ceil(totalItems / itemsPerPage);

        var paginationHtml = "";
        for (var i = 1; i <= totalPages; i++) {
            paginationHtml += "<li class='page-item" + (i === currentPage ? " active" : "") + "'><a class='page-link' href='#'>" + i + "</a></li>";
        }

        $("#pagination").html(paginationHtml);

        // Pagination click event handler
        $(".page-link").click(function (e) {
            e.preventDefault();
            currentPage = parseInt($(this).text());
            displayUsers();
        });
    }
});
