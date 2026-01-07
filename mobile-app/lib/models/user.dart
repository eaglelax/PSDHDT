class User {
  final int id;
  final String matricule;
  final String nom;
  final String prenom;
  final String email;
  final String role;
  final double? salaireBase;
  final bool actif;

  User({
    required this.id,
    required this.matricule,
    required this.nom,
    required this.prenom,
    required this.email,
    required this.role,
    this.salaireBase,
    required this.actif,
  });

  String get nomComplet => '$prenom $nom';

  bool get isEmploye => role == 'employe';
  bool get isGardien => role == 'gardien';
  bool get isRH => role == 'rh';
  bool get isDirecteur => role == 'directeur';

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      matricule: json['matricule'] ?? '',
      nom: json['nom'] ?? '',
      prenom: json['prenom'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? 'employe',
      salaireBase: json['salaire_base'] != null
          ? double.tryParse(json['salaire_base'].toString())
          : null,
      actif: json['actif'] ?? true,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'matricule': matricule,
      'nom': nom,
      'prenom': prenom,
      'email': email,
      'role': role,
      'salaire_base': salaireBase,
      'actif': actif,
    };
  }
}
