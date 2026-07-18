import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../api_client.dart';
import '../core/storage/local_database.dart';
import '../features/sync/sync_queue_service.dart';
import 'balance_screen.dart';
import 'cash_session_screen.dart';
import 'claims_screen.dart';
import 'commissions_screen.dart';
import 'fiche_detail_screen.dart';
import 'fiches_screen.dart';
import 'gains_screen.dart';
import 'login_screen.dart';
import 'new_fiche_screen.dart';
import 'notifications_screen.dart';
import 'pay_gain_screen.dart';
import 'qr_verify_screen.dart';
import 'tirages_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? data;
  String agentName = 'Agent';
  int pendingSync = 0;
  int unreadNotifications = 0;
  bool loading = false;

  Future<void> load() async {
    setState(() => loading = true);
    try {
      final prefs = await SharedPreferences.getInstance();
      agentName = prefs.getString('mobile_user_name') ?? 'Agent';
      pendingSync = await LocalDatabase.instance.pendingCount();
      final res = await ApiClient().get('dashboard.php');
      data = res['data'] as Map<String, dynamic>?;
      unreadNotifications = int.tryParse('${data?['unread_notifications'] ?? 0}') ?? 0;
    } catch (e) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> sync() async {
    try {
      final count = await SyncQueueService().syncPendingTickets();
      await load();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('$count ticket(s) synchronisé(s).')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.toString())));
    }
  }

  Future<void> logout() async {
    try { await ApiClient().get('logout.php'); } catch (_) {}
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    if (!mounted) return;
    Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
  }

  @override
  void initState() {
    super.initState();
    load();
  }

  double money(Object? value) {
    if (value is num) return value.toDouble();
    return double.tryParse('${value ?? 0}') ?? 0.0;
  }

  Widget kpiCard(String title, Object? value, IconData icon, {bool isMoney = false}) {
    final display = isMoney ? '${money(value).toStringAsFixed(2)} HTG' : '${value ?? 0}';
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [Icon(icon, size: 20), const SizedBox(width: 8), Expanded(child: Text(title, maxLines: 1, overflow: TextOverflow.ellipsis))]),
          const Spacer(),
          Text(display, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 19, fontWeight: FontWeight.bold)),
        ]),
      ),
    );
  }

  Future<void> openPage(Widget page) async {
    await Navigator.push(context, MaterialPageRoute(builder: (_) => page));
    if (mounted) load();
  }

  Widget actionCard({
    required String title,
    required IconData icon,
    required Widget page,
    Color? color,
    int badgeCount = 0,
  }) {
    final scheme = Theme.of(context).colorScheme;
    final baseColor = color ?? scheme.primary;
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () => openPage(page),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Stack(children: [
            Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              CircleAvatar(
                backgroundColor: baseColor.withOpacity(.12),
                foregroundColor: baseColor,
                child: Icon(icon),
              ),
              const Spacer(),
              Text(title, style: const TextStyle(fontWeight: FontWeight.w700), maxLines: 2, overflow: TextOverflow.ellipsis),
            ]),
            if (badgeCount > 0)
              Positioned(
                right: 0,
                top: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                  decoration: BoxDecoration(color: Colors.red, borderRadius: BorderRadius.circular(99)),
                  child: Text('$badgeCount', style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold)),
                ),
              ),
          ]),
        ),
      ),
    );
  }

  void handleBottomNav(int index) {
    switch (index) {
      case 1:
        openPage(const FichesScreen());
        break;
      case 2:
        openPage(const CommissionsScreen());
        break;
      case 3:
        openPage(const NotificationsScreen());
        break;
      case 4:
        showModalBottomSheet(
          context: context,
          showDragHandle: true,
          builder: (context) => SafeArea(
            child: Column(mainAxisSize: MainAxisSize.min, children: [
              ListTile(leading: const Icon(Icons.account_circle), title: Text(agentName), subtitle: const Text('Profil agent')),
              ListTile(leading: const Icon(Icons.sync), title: const Text('Synchroniser maintenant'), onTap: () { Navigator.pop(context); sync(); }),
              ListTile(leading: const Icon(Icons.point_of_sale), title: const Text('Cash session'), onTap: () { Navigator.pop(context); openPage(const CashSessionScreen()); }),
              ListTile(leading: const Icon(Icons.logout), title: const Text('Déconnexion'), onTap: () { Navigator.pop(context); logout(); }),
            ]),
          ),
        );
        break;
      default:
        load();
    }
  }

  @override
  Widget build(BuildContext context) {
    final wide = MediaQuery.of(context).size.width > 700;
    return Scaffold(
      appBar: AppBar(
        title: Text('Bonjour, $agentName'),
        actions: [
          IconButton(onPressed: sync, tooltip: 'Sync', icon: const Icon(Icons.sync)),
          Stack(children: [
            IconButton(onPressed: () => openPage(const NotificationsScreen()), tooltip: 'Notifications', icon: const Icon(Icons.notifications_outlined)),
            if (unreadNotifications > 0)
              Positioned(
                right: 8,
                top: 8,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                  decoration: BoxDecoration(color: Colors.red, borderRadius: BorderRadius.circular(99)),
                  child: Text('$unreadNotifications', style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                ),
              ),
          ]),
          IconButton(onPressed: logout, tooltip: 'Logout', icon: const Icon(Icons.logout)),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: 0,
        onDestinationSelected: handleBottomNav,
        destinations: [
          const NavigationDestination(icon: Icon(Icons.home_outlined), selectedIcon: Icon(Icons.home), label: 'Home'),
          const NavigationDestination(icon: Icon(Icons.receipt_long_outlined), selectedIcon: Icon(Icons.receipt_long), label: 'Tickets'),
          const NavigationDestination(icon: Icon(Icons.percent_outlined), selectedIcon: Icon(Icons.percent), label: 'Commission'),
          NavigationDestination(
            icon: Badge(isLabelVisible: unreadNotifications > 0, label: Text('$unreadNotifications'), child: const Icon(Icons.notifications_outlined)),
            selectedIcon: Badge(isLabelVisible: unreadNotifications > 0, label: Text('$unreadNotifications'), child: const Icon(Icons.notifications)),
            label: 'Notifications',
          ),
          const NavigationDestination(icon: Icon(Icons.person_outline), selectedIcon: Icon(Icons.person), label: 'Profil'),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => openPage(const NewFicheScreen()),
        icon: const Icon(Icons.add),
        label: const Text('Nouvelle fiche'),
      ),
      body: RefreshIndicator(
        onRefresh: load,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (loading) const LinearProgressIndicator(),
            GridView.count(
              crossAxisCount: wide ? 4 : 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              childAspectRatio: wide ? 1.45 : 1.25,
              children: [
                kpiCard('Fiches jodi a', data?['today_fiches'], Icons.receipt_long),
                kpiCard('Ventes', data?['today_sales'], Icons.payments, isMoney: true),
                kpiCard('Gains', data?['today_gains'], Icons.emoji_events, isMoney: true),
                kpiCard('Gains payés', data?['today_gains_paid'], Icons.payments_outlined, isMoney: true),
                kpiCard('Commission', data?['today_commission'], Icons.percent, isMoney: true),
                kpiCard('Encaisse', data?['cash_on_hand'], Icons.account_balance_wallet, isMoney: true),
                kpiCard('À remettre', data?['amount_to_remit'] ?? data?['balance'], Icons.account_balance, isMoney: true),
              ],
            ),
            if (pendingSync > 0)
              Card(
                color: Colors.orange.shade50,
                child: ListTile(
                  leading: const Icon(Icons.cloud_off),
                  title: Text('$pendingSync ticket(s) ap tann sync'),
                  subtitle: const Text('Peze Sync lè entènèt disponib.'),
                  trailing: FilledButton(onPressed: sync, child: const Text('Sync')),
                ),
              ),
            const SizedBox(height: 12),
            Text('Opérations', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            GridView.count(
              crossAxisCount: wide ? 4 : 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              childAspectRatio: 1.35,
              children: [
                actionCard(title: 'Nouvelle fiche', icon: Icons.add_circle, page: const NewFicheScreen(), color: Colors.green),
                actionCard(title: 'Mes fiches', icon: Icons.list_alt, page: const FichesScreen(), color: Colors.blue),
                actionCard(title: 'Cash session', icon: Icons.point_of_sale, page: const CashSessionScreen(), color: Colors.deepPurple),
                actionCard(title: 'Scanner QR', icon: Icons.qr_code_scanner, page: const QrVerifyScreen(), color: Colors.indigo),
                actionCard(title: 'Payer gain', icon: Icons.payments, page: const PayGainScreen(), color: Colors.amber),
              ],
            ),
            const SizedBox(height: 12),
            Text('Finance & communication', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            GridView.count(
              crossAxisCount: wide ? 4 : 2,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              childAspectRatio: 1.35,
              children: [
                actionCard(title: 'Commissions', icon: Icons.percent, page: const CommissionsScreen(), color: Colors.teal),
                actionCard(title: 'Gains', icon: Icons.emoji_events, page: const GainsScreen(), color: Colors.amber),
                actionCard(title: 'Notifications', icon: Icons.notifications, page: const NotificationsScreen(), color: Colors.red, badgeCount: unreadNotifications),
                actionCard(title: 'Claims gains', icon: Icons.assignment_turned_in, page: const ClaimsScreen(), color: Colors.orange),
                actionCard(title: 'Situation financière', icon: Icons.account_balance, page: const BalanceScreen(), color: Colors.brown),
                actionCard(title: 'Tirages', icon: Icons.casino, page: const TiragesScreen(), color: Colors.pink),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
