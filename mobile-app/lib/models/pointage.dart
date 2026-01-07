class Pointage {
  final int id;
  final int userId;
  final String type; // 'entree' ou 'sortie'
  final DateTime horodatage;
  final String? methode;
  final User? user;

  Pointage({
    required this.id,
    required this.userId,
    required this.type,
    required this.horodatage,
    this.methode,
    this.user,
  });

  bool get isEntree => type == 'entree';
  bool get isSortie => type == 'sortie';

  factory Pointage.fromJson(Map<String, dynamic> json) {
    return Pointage(
      id: json['id'],
      userId: json['user_id'],
      type: json['type'],
      horodatage: DateTime.parse(json['horodatage'] ?? json['date'] + ' ' + json['heure']),
      methode: json['methode'],
      user: json['user'] != null ? User.fromJson(json['user']) : null,
    );
  }
}

class User {
  final int id;
  final String matricule;
  final String nom;
  final String prenom;

  User({
    required this.id,
    required this.matricule,
    required this.nom,
    required this.prenom,
  });

  String get nomComplet => '$prenom $nom';

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      matricule: json['matricule'] ?? '',
      nom: json['nom'] ?? '',
      prenom: json['prenom'] ?? '',
    );
  }
}
