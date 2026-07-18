import 'package:flutter/material.dart';
import 'package:uuid/uuid.dart';
import '../config.dart';
import '../core/storage/local_database.dart';
import '../models/lottery.dart';
import '../models/game_type.dart';
import '../models/ticket_play.dart';
import '../models/tirage.dart';
import '../repositories/lottery_repository.dart';
import '../repositories/game_repository.dart';
import '../repositories/ticket_repository.dart';
import '../services/ticket_validation_service.dart';
import '../widgets/ticket_preview_dialog.dart';

class NewFicheScreen extends StatefulWidget {
  const NewFicheScreen({super.key});

  @override
  State<NewFicheScreen> createState() => _NewFicheScreenState();
}

class _NewFicheScreenState extends State<NewFicheScreen> {
  final LotteryRepository lotteryRepository = LotteryRepository();
  final GameRepository gameRepository = GameRepository();
  final TicketRepository ticketRepository = TicketRepository();
  final List<Map<String, TextEditingController>> lines = [];
  final List<String> types = [];

  List<Lottery> lotteries = [];
  List<GameType> gameTypes = [];
  List<Tirage> tirages = [];
  Lottery? selectedLottery;
  Tirage? selectedTirage;
  bool loading = false;
  bool loadingLotteries = false;
  bool loadingTirages = false;
  bool loadingGames = false;

  @override
  void initState() {
    super.initState();
    addLine();
    loadLotteries();
    loadGames();
  }

  @override
  void dispose() {
    for (final line in lines) {
      line['number']?.dispose();
      line['amount']?.dispose();
    }
    super.dispose();
  }

  Future<void> loadLotteries() async {
    setState(() => loadingLotteries = true);
    try {
      final rows = await lotteryRepository.listOpenLotteries();
      if (!mounted) return;
      setState(() {
        lotteries = rows;
        if (selectedLottery != null && !lotteries.any((l) => l.id == selectedLottery!.id)) {
          selectedLottery = null;
          selectedTirage = null;
          tirages = [];
        }
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Erreur lotteries: $e')));
    } finally {
      if (mounted) setState(() => loadingLotteries = false);
    }
  }

  Future<void> loadGames() async {
    setState(() => loadingGames = true);
    try {
      final rows = await gameRepository.listEnabledGames();
      if (!mounted) return;
      setState(() {
        gameTypes = rows;
        final defaultCode = gameTypes.isNotEmpty ? gameTypes.first.code : 'borlette';
        for (var i = 0; i < types.length; i++) {
          if (!gameTypes.any((game) => game.code == types[i])) types[i] = defaultCode;
        }
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Erreur jeux: $e')));
    } finally {
      if (mounted) setState(() => loadingGames = false);
    }
  }

  Future<void> loadTiragesForLottery(Lottery? lottery) async {
    selectedTirage = null;
    tirages = [];
    if (lottery == null) {
      setState(() {});
      return;
    }
    setState(() => loadingTirages = true);
    try {
      final rows = await lotteryRepository.listTirages(lotteryId: lottery.id);
      if (!mounted) return;
      setState(() => tirages = rows);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Erreur tirages: $e')));
    } finally {
      if (mounted) setState(() => loadingTirages = false);
    }
  }

  void addLine() {
    setState(() {
      final number = TextEditingController();
      final amount = TextEditingController();
      number.addListener(_refreshTotal);
      amount.addListener(_refreshTotal);
      lines.add({'number': number, 'amount': amount});
      types.add(gameTypes.isNotEmpty ? gameTypes.first.code : 'borlette');
    });
  }

  void _refreshTotal() {
    if (mounted) setState(() {});
  }

  void removeLine(int index) {
    if (lines.length == 1) return;
    setState(() {
      lines[index]['number']?.dispose();
      lines[index]['amount']?.dispose();
      lines.removeAt(index);
      types.removeAt(index);
    });
  }

  List<TicketPlay> buildPlays() {
    final plays = <TicketPlay>[];
    for (var i = 0; i < lines.length; i++) {
      plays.add(TicketPlay(
        number: lines[i]['number']!.text.trim(),
        type: types[i],
        amount: double.tryParse(lines[i]['amount']!.text.replaceAll(',', '.')) ?? 0,
      ));
    }
    return plays;
  }

  double totalAmount(List<TicketPlay> plays) => plays.fold(0.0, (sum, p) => sum + p.amount);

  Future<void> save() async {
    final lottery = selectedLottery;
    if (lottery == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Chwazi yon lottery avan.')));
      return;
    }
    if (!lottery.isOpen) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cette loterie est actuellement fermée.')));
      return;
    }

    final localUuid = const Uuid().v4();
    final plays = buildPlays();
    final total = totalAmount(plays);
    final validationError = TicketValidationService.validateTicket(plays, gameTypes);
    if (validationError != null) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(validationError)));
      return;
    }

    final confirmed = await showTicketPreviewDialog(
      context: context,
      lottery: lottery,
      tirage: selectedTirage,
      plays: plays,
      total: total,
    );
    if (!confirmed) return;

    final payload = {
      'device_id': defaultDeviceId,
      'local_uuid': localUuid,
      'lottery_id': lottery.id,
      'tirage_id': selectedTirage?.id,
      'plays': plays.map((p) => p.toJson()).toList(),
    };

    setState(() => loading = true);
    try {
      final res = await ticketRepository.store(payload);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(res['message'] ?? 'Fiche enregistrée')));
      if (res['success'] == true) Navigator.pop(context);
    } catch (e) {
      await LocalDatabase.instance.savePendingTicket(
        localUuid: localUuid,
        payload: payload,
        totalAmount: total,
        errorMessage: e.toString(),
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Serveur indisponible: ticket gardé localement pou sync. ($e)')),
      );
      Navigator.pop(context);
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  GameType? _gameForCode(String code) {
    for (final game in gameTypes) {
      if (game.code == code) return game;
    }
    return null;
  }


  @override
  Widget build(BuildContext context) {
    final plays = buildPlays();
    final total = totalAmount(plays);
    final lotteryClosed = selectedLottery != null && !selectedLottery!.isOpen;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Nouvelle fiche'),
        actions: [IconButton(onPressed: loadingLotteries ? null : loadLotteries, icon: const Icon(Icons.refresh))],
      ),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        if (loadingLotteries) const LinearProgressIndicator(),
        DropdownButtonFormField<Lottery>(
          value: selectedLottery,
          items: [
            for (final lottery in lotteries)
              DropdownMenuItem<Lottery>(
                value: lottery,
                child: Text('${lottery.name} • ${lottery.salesStatus}${lottery.drawTime == null ? '' : ' • ${lottery.drawTime}'}'),
              ),
          ],
          onChanged: (lottery) {
            setState(() => selectedLottery = lottery);
            loadTiragesForLottery(lottery);
          },
          decoration: InputDecoration(
            labelText: 'Lottery',
            helperText: lotteries.isEmpty ? 'Aucune lottery ouverte pou kont sa.' : null,
          ),
        ),
        if (lotteryClosed)
          Card(
            color: Colors.red.shade50,
            child: const ListTile(
              leading: Icon(Icons.lock, color: Colors.red),
              title: Text('Cette loterie est actuellement fermée.'),
              subtitle: Text('Agent pa ka vann sou lottery sa.'),
            ),
          ),
        const SizedBox(height: 12),
        if (loadingTirages) const LinearProgressIndicator(),
        DropdownButtonFormField<Tirage?>(
          value: selectedTirage,
          items: [
            const DropdownMenuItem<Tirage?>(value: null, child: Text('Aucun tirage précis')),
            for (final tirage in tirages)
              DropdownMenuItem<Tirage?>(
                value: tirage,
                child: Text('${tirage.drawName} ${tirage.drawDate ?? ''}'),
              ),
          ],
          onChanged: (tirage) => setState(() => selectedTirage = tirage),
          decoration: const InputDecoration(labelText: 'Tirage'),
        ),
        const SizedBox(height: 12),
        if (loadingGames) const LinearProgressIndicator(),
        for (var i = 0; i < lines.length; i++)
          Card(
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: Column(children: [
                Row(children: [
                  Expanded(child: Text('Ligne ${i + 1}', style: const TextStyle(fontWeight: FontWeight.bold))),
                  IconButton(onPressed: () => removeLine(i), icon: const Icon(Icons.delete_outline)),
                ]),
                DropdownButtonFormField<String>(
                  value: types[i],
                  items: [
                    for (final game in gameTypes)
                      DropdownMenuItem(value: game.code, child: Text(game.name)),
                  ],
                  onChanged: (v) => setState(() => types[i] = v ?? (gameTypes.isNotEmpty ? gameTypes.first.code : 'borlette')),
                  decoration: const InputDecoration(labelText: 'Jeu'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: lines[i]['number'],
                  keyboardType: (_gameForCode(types[i])?.validationPattern ?? '').contains('-') ? TextInputType.text : TextInputType.number,
                  decoration: InputDecoration(labelText: 'Numéro', helperText: _gameForCode(types[i])?.inputHint ?? 'Numéro'),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: lines[i]['amount'],
                  keyboardType: const TextInputType.numberWithOptions(decimal: true),
                  decoration: const InputDecoration(labelText: 'Montant'),
                ),
              ]),
            ),
          ),
        OutlinedButton.icon(onPressed: addLine, icon: const Icon(Icons.add), label: const Text('Ajouter ligne')),
        const SizedBox(height: 8),
        Card(
          child: ListTile(
            title: Text('${plays.length} ligne(s)'),
            subtitle: const Text('Total an tan reyèl'),
            trailing: Text('${total.toStringAsFixed(2)} HTG', style: const TextStyle(fontWeight: FontWeight.bold)),
          ),
        ),
        const SizedBox(height: 8),
        FilledButton.icon(
          onPressed: loading || lotteryClosed ? null : save,
          icon: const Icon(Icons.save),
          label: Text(loading ? 'Enregistrement...' : 'Prévisualiser / Enregistrer'),
        ),
      ]),
    );
  }
}
