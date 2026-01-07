import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../services/api_service.dart';

class HistoryScreen extends StatefulWidget {
  const HistoryScreen({super.key});

  @override
  State<HistoryScreen> createState() => _HistoryScreenState();
}

class _HistoryScreenState extends State<HistoryScreen>
    with SingleTickerProviderStateMixin {
  final _apiService = ApiService();
  late TabController _tabController;

  List<dynamic> _pointages = [];
  List<dynamic> _sessions = [];
  bool _isLoading = true;
  String? _errorMessage;

  // Filtres
  DateTime _startDate = DateTime.now().subtract(const Duration(days: 30));
  DateTime _endDate = DateTime.now();

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final dateFormat = DateFormat('yyyy-MM-dd');

      final [pointagesRes, sessionsRes] = await Future.wait([
        _apiService.getMyPointages(
          dateDebut: dateFormat.format(_startDate),
          dateFin: dateFormat.format(_endDate),
        ),
        _apiService.getMySessions(
          dateDebut: dateFormat.format(_startDate),
          dateFin: dateFormat.format(_endDate),
        ),
      ]);

      if (pointagesRes['success'] == true) {
        _pointages = pointagesRes['data'] is List
            ? pointagesRes['data']
            : (pointagesRes['data']['data'] ?? []);
      }

      if (sessionsRes['success'] == true) {
        _sessions = sessionsRes['data'] is List
            ? sessionsRes['data']
            : (sessionsRes['data']['data'] ?? []);
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Erreur de chargement des donnees';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _selectDateRange() async {
    final picked = await showDateRangePicker(
      context: context,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      initialDateRange: DateTimeRange(start: _startDate, end: _endDate),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: Color(0xFF4F46E5),
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _startDate = picked.start;
        _endDate = picked.end;
      });
      _loadData();
    }
  }

  @override
  Widget build(BuildContext context) {
    final dateFormat = DateFormat('dd/MM/yyyy');

    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique'),
        backgroundColor: const Color(0xFF4F46E5),
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'Pointages'),
            Tab(text: 'Sessions'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Filtre date
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.grey[100],
            child: InkWell(
              onTap: _selectDateRange,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey[300]!),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.date_range,
                      color: Color(0xFF4F46E5),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        '${dateFormat.format(_startDate)} - ${dateFormat.format(_endDate)}',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                    Icon(
                      Icons.arrow_drop_down,
                      color: Colors.grey[600],
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Contenu
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                      color: Color(0xFF4F46E5),
                    ),
                  )
                : _errorMessage != null
                    ? _buildError()
                    : TabBarView(
                        controller: _tabController,
                        children: [
                          _buildPointagesList(),
                          _buildSessionsList(),
                        ],
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 64,
            color: Colors.red[400],
          ),
          const SizedBox(height: 16),
          Text(
            _errorMessage!,
            style: TextStyle(
              fontSize: 16,
              color: Colors.red[700],
            ),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: _loadData,
            icon: const Icon(Icons.refresh),
            label: const Text('Reessayer'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4F46E5),
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPointagesList() {
    if (_pointages.isEmpty) {
      return _buildEmptyState('Aucun pointage pour cette periode');
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      color: const Color(0xFF4F46E5),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _pointages.length,
        itemBuilder: (context, index) {
          final pointage = _pointages[index];
          final isEntree = pointage['type'] == 'entree';

          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: ListTile(
              leading: Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: isEntree
                      ? Colors.green[50]
                      : Colors.red[50],
                  shape: BoxShape.circle,
                ),
                child: Icon(
                  isEntree ? Icons.login : Icons.logout,
                  color: isEntree ? Colors.green : Colors.red,
                ),
              ),
              title: Text(
                isEntree ? 'Entree' : 'Sortie',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                ),
              ),
              subtitle: Text(
                _formatDate(pointage['date']),
                style: TextStyle(
                  color: Colors.grey[600],
                ),
              ),
              trailing: Text(
                pointage['heure'] ?? '--:--',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF4F46E5),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildSessionsList() {
    if (_sessions.isEmpty) {
      return _buildEmptyState('Aucune session pour cette periode');
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      color: const Color(0xFF4F46E5),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _sessions.length,
        itemBuilder: (context, index) {
          final session = _sessions[index];
          final isComplete = session['heure_sortie'] != null ||
              session['heure_depart'] != null;
          final heuresNormales = session['heures_normales'] ?? 0;
          final heuresSup = session['heures_supplementaires'] ?? 0;

          return Card(
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Date et statut
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        _formatDate(session['date']),
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: isComplete
                              ? Colors.green[100]
                              : Colors.orange[100],
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Text(
                          isComplete ? 'Termine' : 'En cours',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.bold,
                            color: isComplete
                                ? Colors.green[800]
                                : Colors.orange[800],
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Horaires
                  Row(
                    children: [
                      Expanded(
                        child: _buildTimeInfo(
                          'Arrivee',
                          session['heure_entree'] ?? session['heure_arrivee'] ?? '--:--',
                          Colors.green,
                        ),
                      ),
                      Container(
                        width: 1,
                        height: 40,
                        color: Colors.grey[300],
                      ),
                      Expanded(
                        child: _buildTimeInfo(
                          'Depart',
                          session['heure_sortie'] ?? session['heure_depart'] ?? '--:--',
                          Colors.red,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Heures
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.grey[100],
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _buildHoursInfo('Normales', '$heuresNormales h'),
                        Container(
                          width: 1,
                          height: 30,
                          color: Colors.grey[300],
                        ),
                        _buildHoursInfo(
                          'Sup.',
                          '$heuresSup h',
                          isHighlighted: heuresSup > 0,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildTimeInfo(String label, String time, Color color) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
        const SizedBox(height: 4),
        Text(
          time,
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
      ],
    );
  }

  Widget _buildHoursInfo(String label, String value, {bool isHighlighted = false}) {
    return Column(
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 12,
            color: Colors.grey[600],
          ),
        ),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: isHighlighted ? Colors.orange[700] : const Color(0xFF4F46E5),
          ),
        ),
      ],
    );
  }

  Widget _buildEmptyState(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.inbox_outlined,
            size: 80,
            color: Colors.grey[400],
          ),
          const SizedBox(height: 16),
          Text(
            message,
            style: TextStyle(
              fontSize: 16,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  String _formatDate(String? dateStr) {
    if (dateStr == null) return 'Date inconnue';
    try {
      final date = DateTime.parse(dateStr);
      return DateFormat('EEEE d MMMM yyyy', 'fr_FR').format(date);
    } catch (e) {
      return dateStr;
    }
  }
}
