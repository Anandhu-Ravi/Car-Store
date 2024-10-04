<?php
include 'dbinit.php';

$name = $description = $quantity = $price = "";
$update = false;
$carPartID = 0;
$errors = [];
$successMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['name'])) {
        $errors['name'] = "Name is required";
    } else {
        $name = htmlspecialchars(trim($_POST['name']));
    }

    if (empty($_POST['description'])) {
        $errors['description'] = "Description is required";
    } else {
        $description = htmlspecialchars(trim($_POST['description']));
    }

    if (empty($_POST['quantity']) || !is_numeric($_POST['quantity']) || $_POST['quantity'] <= 0) {
        $errors['quantity'] = "Please enter a valid quantity";
    } else {
        $quantity = intval($_POST['quantity']);
    }

    if (empty($_POST['price']) || !is_numeric($_POST['price']) || $_POST['price'] <= 0) {
        $errors['price'] = "Please enter a valid price";
    } else {
        $price = floatval($_POST['price']);
    }

    if (count($errors) == 0) {
        $conn = new mysqli($host, $user, $password, $dbname);

        if (isset($_POST['create'])) {
            // create 
            $stmt = $conn->prepare("INSERT INTO car_parts (CarPartName, CarPartDescription, QuantityAvailable, Price, ProductAddedBy) VALUES (?, ?, ?, ?, 'Anandhu Ravi')");
            $stmt->bind_param("ssii", $name, $description, $quantity, $price);

            if ($stmt->execute()) {
                $successMessage = "Part added successfully!";
            } else {
                $errors['general'] = "Error adding part: " . $conn->error;
            }

            $stmt->close();
        } elseif (isset($_POST['update'])) {
            // update 
            $carPartID = $_POST['carPartID'];
            $stmt = $conn->prepare("UPDATE car_parts SET CarPartName = ?, CarPartDescription = ?, QuantityAvailable = ?, Price = ? WHERE CarPartID = ?");
            $stmt->bind_param("ssiii", $name, $description, $quantity, $price, $carPartID);

            if ($stmt->execute()) {
                $successMessage = "Part updated successfully!";
            } else {
                $errors['general'] = "Error updating part: " . $conn->error;
            }

            $stmt->close();
        }

        $conn->close();
    }
}

// delete 
if (isset($_GET['delete'])) {
    $carPartID = $_GET['delete'];
    $conn = new mysqli($host, $user, $password, $dbname);
    $stmt = $conn->prepare("DELETE FROM car_parts WHERE CarPartID = ?");
    $stmt->bind_param("i", $carPartID);

    if ($stmt->execute()) {
        $successMessage = "Part deleted successfully!";
    } else {
        $errors['general'] = "Error deleting part: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

// edit
if (isset($_GET['edit'])) {
    $carPartID = $_GET['edit'];
    $conn = new mysqli($host, $user, $password, $dbname);
    $stmt = $conn->prepare("SELECT * FROM car_parts WHERE CarPartID = ?");
    $stmt->bind_param("i", $carPartID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $name = $row['CarPartName'];
        $description = $row['CarPartDescription'];
        $quantity = $row['QuantityAvailable'];
        $price = $row['Price'];
        $update = true;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Car Parts Inventory</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1 class="text-center">Car Parts Inventory</h1>

    <?php if ($successMessage): ?>
        <div class="alert alert-success" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($errors['general'])): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $errors['general']; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF']; ?>">
            <input type="hidden" name="carPartID" value="<?php echo $carPartID; ?>">

            <div class="form-group">
                <label for="name">Part Name</label>
                <input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" value="<?php echo $name; ?>">
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $errors['name']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="description">Part Description</label>
                <textarea name="description" class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>"><?php echo $description; ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $errors['description']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity Available</label>
                <input type="number" name="quantity" class="form-control <?php echo isset($errors['quantity']) ? 'is-invalid' : ''; ?>" value="<?php echo $quantity; ?>">
                <?php if (isset($errors['quantity'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $errors['quantity']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" step="0.01" name="price" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" value="<?php echo $price; ?>">
                <?php if (isset($errors['price'])): ?>
                    <div class="invalid-feedback">
                        <?php echo $errors['price']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group buton">
                <?php if ($update): ?>
                <button type="submit" name="update" class="btn btn-primary">Update Car Part</button>
                <?php else: ?>
                <button type="submit" name="create" class="btn btn-success">Add Car Part</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-container">
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Quantity Available</th>
                    <th>Price</th>
                    <th>Product Added By</th>
                    <th>Date Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $conn = new mysqli($host, $user, $password, $dbname);
                $sql = "SELECT * FROM car_parts";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['CarPartID']}</td>
                                <td>{$row['CarPartName']}</td>
                                <td>{$row['CarPartDescription']}</td>
                                <td>{$row['QuantityAvailable']}</td>
                                <td>{$row['Price']}</td>
                                <td>{$row['ProductAddedBy']}</td>
                                <td>{$row['DateAdded']}</td>
                                <td>
                                    <a href='?edit={$row['CarPartID']}' class='btn btn-warning'>Edit</a>
                                    <a href='?delete={$row['CarPartID']}' class='btn btn-danger'>Delete</a>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>No car parts found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
