import '../api_client.dart';

class TicketRepository {
  final ApiClient api;
  TicketRepository({ApiClient? api}) : api = api ?? ApiClient();

  Future<Map<String, dynamic>> store(Map<String, dynamic> payload) {
    return api.postJson('fiches/store.php', payload);
  }
}
