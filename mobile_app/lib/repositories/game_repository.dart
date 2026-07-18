import '../api_client.dart';
import '../models/game_type.dart';

class GameRepository {
  final ApiClient api;
  GameRepository({ApiClient? api}) : api = api ?? ApiClient();

  Future<List<GameType>> listEnabledGames() async {
    final res = await api.get('game_types_list.php');
    final rows = (res['data'] as List?) ?? const [];
    return rows
        .whereType<Map>()
        .map((row) => GameType.fromJson(Map<String, dynamic>.from(row)))
        .where((game) => game.code.isNotEmpty)
        .toList();
  }
}
