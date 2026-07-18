import '../api_client.dart';
import '../models/lottery.dart';
import '../models/tirage.dart';

class LotteryRepository {
  final ApiClient api;
  LotteryRepository({ApiClient? api}) : api = api ?? ApiClient();

  Future<List<Lottery>> listOpenLotteries() async {
    final res = await api.get('lotteries_list.php');
    final rows = (res['data'] as List?) ?? [];
    return rows
        .whereType<Map>()
        .map((row) => Lottery.fromJson(Map<String, dynamic>.from(row)))
        .where((lottery) => lottery.id > 0)
        .toList();
  }

  Future<List<Tirage>> listTirages({int? lotteryId}) async {
    final endpoint = lotteryId == null ? 'tirages_list.php' : 'tirages_list.php?lottery_id=$lotteryId';
    final res = await api.get(endpoint);
    final rows = (res['data'] as List?) ?? [];
    return rows
        .whereType<Map>()
        .map((row) => Tirage.fromJson(Map<String, dynamic>.from(row)))
        .where((tirage) => tirage.id > 0)
        .toList();
  }
}
