class Config {
  // URL de l'API - A modifier selon l'environnement
  static const String apiUrl = 'http://localhost:8000/api'; // Pour Web/Desktop
  // static const String apiUrl = 'http://10.0.2.2:8080/api'; // Pour emulateur Android
  // static const String apiUrl = 'http://192.168.x.x:8080/api'; // Pour device physique (mettre IP locale)

  // Cles de stockage local
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';

  // Duree de validite QR code (en minutes)
  static const int qrCodeValidityMinutes = 5;
}
