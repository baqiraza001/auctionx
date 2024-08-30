<?php

namespace NataInditama\Auctionx\Models;

use Exception;
use mysqli_stmt;
use NataInditama\Auctionx\App\Database;

class Barang extends Database
{
  var int $id_barang;
  var string $nama_barang;
  var string $tgl;
  var int $harga_awal;
  var string $deskripsi_barang;

  public function findByBarangId(int $id): ?array
  {
    $query = "SELECT `id_barang`, `nama_barang`, `tgl`, `harga_awal`, `deskripsi_barang` FROM `tb_barang` WHERE `id_barang` = ?";

    $statement = $this->mysqli->prepare($query);
    $statement->bind_param("i", $id);
    $statement->execute();

    $result = $statement->get_result();
    return $result->fetch_assoc();
  }

  public function findAll(): ?array
  {
    $query = "SELECT `id_barang`, `nama_barang`, `tgl`, `harga_awal`, `deskripsi_barang` FROM `tb_barang`";

    $statement = $this->mysqli->prepare($query);
    $statement->execute();

    $result = $statement->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function save(Barang $request): mysqli_stmt|bool
  {
    $query = "INSERT INTO `tb_barang`(`nama_barang`, `tgl`, `harga_awal`, `deskripsi_barang`) VALUES (?,?,?,?)";

    $statement = $this->mysqli->prepare($query);
    $statement->bind_param("ssis", $request->nama_barang, $request->tgl, $request->harga_awal, $request->deskripsi_barang);
    $statement->execute();

    return $statement;
  }

  public function deleteByBarangId(string $barangId): void
  {
    $query = "DELETE FROM `tb_barang` WHERE `id_barang` = ?";

    $data = $this->findByBarangId($barangId);
    if (is_null($data)) throw new Exception("Data could not be found");

    $statement = $this->mysqli->prepare($query);
    $statement->bind_param('s', $barangId);
    $statement->execute();
  }

  public function updateByBarangId(Barang $data): void
  {
    $query = "UPDATE `tb_barang` SET `nama_barang`=?,`tgl`=?,`harga_awal`=?,`deskripsi_barang`=? WHERE `id_barang` = ?";

    $statement = $this->mysqli->prepare($query);
    $statement->bind_param("ssiss", $data->nama_barang, $data->tgl, $data->harga_awal, $data->deskripsi_barang, $data->id_barang);
    $statement->execute();
  }

  public function save_images(int $auction_id): void
  {
      // Check if $_FILES is not empty
      if (!isset($_FILES['auction_images']) || empty($_FILES['auction_images']['tmp_name'])) {
          return; // Exit if there are no images to upload
      }

      // Define the upload directory
      $uploadDir = AUTCTION_IMAGES_PATH;

      // Check if the directory exists, if not, create it
      if (!is_dir($uploadDir)) {
          mkdir($uploadDir, 0755, true);
      }

      // Fetch and delete existing images from the database
      $existingImagesQuery = "SELECT `name` FROM `auction_images` WHERE `autction_id` = ?";
      $deleteImagesQuery = "DELETE FROM `auction_images` WHERE `autction_id` = ?";

      $stmt = $this->mysqli->prepare($existingImagesQuery);
      $stmt->bind_param("i", $auction_id);
      $stmt->execute();
      $result = $stmt->get_result();

      // Unlink existing images from the filesystem
      while ($row = $result->fetch_assoc()) {
          $filePath = $uploadDir . '/' . $row['name'];
          if (file_exists($filePath)) {
              unlink($filePath);
          }
      }

      // Delete existing images records from the database
      $stmt = $this->mysqli->prepare($deleteImagesQuery);
      $stmt->bind_param("i", $auction_id);
      $stmt->execute();

      // Upload new images and insert records into the database
      foreach ($_FILES['auction_images']['tmp_name'] as $key => $tmpName) {
          $fileName = basename($_FILES['auction_images']['name'][$key]);
          $targetFilePath = $uploadDir . '/' . $fileName;

          if (move_uploaded_file($tmpName, $targetFilePath)) {
              $insertImageQuery = "INSERT INTO `auction_images` (`autction_id`, `name`) VALUES (?, ?)";
              $stmt = $this->mysqli->prepare($insertImageQuery);
              $stmt->bind_param("is", $auction_id, $fileName);
              $stmt->execute();
          }
      }
  }

  public function get_images(int $auction_id): array
  {
      $query = "SELECT `image_id`, `autction_id`, `name` FROM `auction_images` WHERE `autction_id` = ?";
      
      $stmt = $this->mysqli->prepare($query);
      $stmt->bind_param("i", $auction_id);
      $stmt->execute();

      $result = $stmt->get_result();
      return $result->fetch_all(MYSQLI_ASSOC);
  }


}
