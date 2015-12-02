class WatchlistPage < ArticlePage

  a(:list_link, text: "List")
  a(:feed_link, text: "Modified")
  
  ul(:page_list_diffs, class: "watchlist page-list")
  ul(:page_list_a_to_z, class: "watchlist page-list thumbs")
end
